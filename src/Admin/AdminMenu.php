<?php
namespace OrderShield\Admin;

class AdminMenu {

    public function init(): void {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function registerMenu(): void {
        add_menu_page(
            'Order Shield',
            'Order Shield',
            'manage_woocommerce', // Capability
            'order-shield',
            [$this, 'renderDashboard'],
            'dashicons-shield',
            56 // Position
        );
    }

    public function enqueueAssets($hook): void {
        if ($hook !== 'toplevel_page_order-shield') {
            return;
        }

        wp_enqueue_style('os-admin-css', OS_ASSETS_URL . 'css/admin.css', [], OS_VERSION);
        wp_enqueue_script('os-admin-js', OS_ASSETS_URL . 'js/admin.js', ['jquery'], OS_VERSION, true);

        wp_localize_script('os-admin-js', 'osData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('os_admin_nonce')
        ]);
    }

    public function renderDashboard(): void {
        // We will include our React/Vue-style vanilla template
        $template = OS_PLUGIN_DIR . 'src/Admin/Views/dashboard.php';
        if (file_exists($template)) {
            require_once $template;
        } else {
            echo "<h2>Error: Dashboard template not found.</h2>";
        }
    }
}
