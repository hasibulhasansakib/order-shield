<?php
namespace OrderShield\Core;

class Updater {
    
    private string $file;
    private string $plugin;
    private string $basename;
    private string $active;
    private string $username;
    private string $repository;
    private ?object $github_response = null;

    public function __construct(string $file) {
        $this->file = $file;
        $this->plugin = plugin_basename($this->file);
        $this->basename = plugin_basename($this->file);
        $this->active = is_plugin_active($this->basename);
        
        $this->username = 'hasibulhasansakib';
        $this->repository = 'order-shield';

        $this->init();
    }

    public function init(): void {
        add_filter('pre_set_site_transient_update_plugins', [$this, 'modify_transient'], 10, 1);
        add_filter('plugins_api', [$this, 'plugin_popup'], 10, 3);
        add_filter('upgrader_post_install', [$this, 'after_install'], 10, 3);
    }

    private function get_repository_info() {
        if (!empty($this->github_response)) {
            return $this->github_response;
        }

        // Fetch the main plugin file from GitHub to read the version header
        $request_uri = sprintf('https://raw.githubusercontent.com/%s/%s/main/order-shield.php', $this->username, $this->repository);

        $response = wp_remote_get($request_uri);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $file_content = wp_remote_retrieve_body($response);
        
        // Extract version using regex
        if (preg_match('/^[ \t]*\*[ \t]*Version:[ \t]*(.+)$/m', $file_content, $matches)) {
            $version = trim($matches[1]);
            
            $this->github_response = (object) [
                'new_version' => $version,
                'zipball_url' => sprintf('https://github.com/%s/%s/archive/refs/heads/main.zip', $this->username, $this->repository)
            ];
            return $this->github_response;
        }

        return false;
    }

    public function modify_transient($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $github_info = $this->get_repository_info();

        if (!$github_info) {
            return $transient;
        }

        $plugin_data = get_plugin_data($this->file);
        $current_version = $plugin_data['Version'];
        $new_version = $github_info->new_version;

        if (version_compare($new_version, $current_version, '>')) {
            $plugin = [
                'url' => $plugin_data['PluginURI'],
                'slug' => current(explode('/', $this->basename)),
                'package' => $github_info->zipball_url,
                'new_version' => $new_version,
            ];

            $transient->response[$this->basename] = (object) $plugin;
        }

        return $transient;
    }

    public function plugin_popup($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return false;
        }

        if (!isset($args->slug) || $args->slug !== current(explode('/', $this->basename))) {
            return $result;
        }

        $github_info = $this->get_repository_info();
        if (!$github_info) {
            return $result;
        }

        $plugin_data = get_plugin_data($this->file);
        $new_version = $github_info->new_version;

        $plugin = [
            'name' => $plugin_data['Name'],
            'slug' => $args->slug,
            'requires' => '5.0',
            'tested' => '6.4',
            'version' => $new_version,
            'author' => $plugin_data['AuthorName'],
            'author_profile' => $plugin_data['AuthorURI'],
            'last_updated' => date('Y-m-d'),
            'homepage' => $plugin_data['PluginURI'],
            'short_description' => $plugin_data['Description'],
            'sections' => [
                'Description' => $plugin_data['Description'],
                'Updates' => 'A new version is available on GitHub (Branch: main).',
            ],
            'download_link' => $github_info->zipball_url,
        ];

        return (object) $plugin;
    }

    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;

        $install_directory = plugin_dir_path($this->file);
        
        // GitHub main.zip extracts to folder like "order-shield-main", we need to move it contents correctly
        $extracted_dir = $result['destination'];
        
        // Usually WP moves it to $install_directory automatically if folder names match, but main.zip has different root
        // If the extracted folder is not the plugin directory, move its contents
        if (basename($extracted_dir) !== basename($install_directory)) {
            $wp_filesystem->move($extracted_dir, $install_directory, true);
            $result['destination'] = $install_directory;
        }

        if ($this->active) {
            activate_plugin($this->basename);
        }

        return $result;
    }
}
