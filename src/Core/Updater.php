<?php
namespace OrderShield\Core;

class Updater {
    
    private string $file;
    private string $plugin;
    private string $basename;
    private string $active;
    private string $username;
    private string $repository;
    private string $github_response;

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
            return json_decode($this->github_response);
        }

        $request_uri = sprintf('https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repository);

        $response = wp_remote_get($request_uri);

        if (is_wp_error($response)) {
            return false;
        }

        $this->github_response = wp_remote_retrieve_body($response);
        return json_decode($this->github_response);
    }

    public function modify_transient($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $github_info = $this->get_repository_info();

        if (!$github_info || !isset($github_info->tag_name)) {
            return $transient;
        }

        $plugin_data = get_plugin_data($this->file);
        $current_version = $plugin_data['Version'];
        // Remove 'v' from tag name if it exists (e.g. 'v1.0.1' -> '1.0.1')
        $new_version = ltrim($github_info->tag_name, 'v');

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
        $new_version = ltrim($github_info->tag_name, 'v');

        $plugin = [
            'name' => $plugin_data['Name'],
            'slug' => $args->slug,
            'requires' => '5.0',
            'tested' => '6.4',
            'version' => $new_version,
            'author' => $plugin_data['AuthorName'],
            'author_profile' => $plugin_data['AuthorURI'],
            'last_updated' => $github_info->published_at,
            'homepage' => $plugin_data['PluginURI'],
            'short_description' => $plugin_data['Description'],
            'sections' => [
                'Description' => $plugin_data['Description'],
                'Updates' => $github_info->body,
            ],
            'download_link' => $github_info->zipball_url,
        ];

        return (object) $plugin;
    }

    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;

        $install_directory = plugin_dir_path($this->file);
        $wp_filesystem->move($result['destination'], $install_directory);
        $result['destination'] = $install_directory;

        if ($this->active) {
            activate_plugin($this->basename);
        }

        return $result;
    }
}
