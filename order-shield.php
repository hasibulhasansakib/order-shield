<?php
/**
 * Plugin Name: Order Shield
 * Plugin URI: https://github.com/hasibulhasansakib/order-shield
 * Description: A production-ready, open-source WooCommerce fraud prevention and order protection system.
 * Version: 1.1.2
 * Author: Hasibul Hasan Sakib
 * Author URI: https://hasibulhasansakib.com
 * Text Domain: order-shield
 * Domain Path: /languages
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define Plugin Constants
define('OS_VERSION', '1.1.2');
define('OS_PLUGIN_FILE', __FILE__);
define('OS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OS_ASSETS_URL', OS_PLUGIN_URL . 'assets/');

// Require Composer Autoloader
$composer_autoload = OS_PLUGIN_DIR . 'vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
} else {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p><strong>Order Shield:</strong> Composer dependencies are missing. Please run <code>composer install</code> inside the plugin directory.</p></div>';
    });
    return;
}

// Initialize the Plugin Core
add_action('plugins_loaded', function() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p><strong>Order Shield:</strong> WooCommerce is missing or deactivated. Order Shield requires WooCommerce to protect your orders. The plugin is currently suspended.</p></div>';
        });
        return;
    }

    if (class_exists(\OrderShield\Core\Installer::class)) {
        \OrderShield\Core\Installer::checkDatabaseVersion();
    }

    if (class_exists(\OrderShield\Core\Plugin::class)) {
        \OrderShield\Core\Plugin::getInstance()->init();
    }
    
    // Initialize Plugin Update Checker for GitHub auto-updates
    if (file_exists(OS_PLUGIN_DIR . 'vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php')) {
        require_once OS_PLUGIN_DIR . 'vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';

        $myUpdateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            'https://github.com/hasibulhasansakib/order-shield/',
            OS_PLUGIN_FILE,
            'order-shield'
        );

        // Set the branch that contains the stable release.
        $myUpdateChecker->setBranch('main');
    }
});

// Register Activation and Deactivation Hooks
register_activation_hook(__FILE__, [\OrderShield\Core\Installer::class, 'activate']);
register_deactivation_hook(__FILE__, [\OrderShield\Core\Installer::class, 'deactivate']);

// Declare HPOS Compatibility
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

// Add Plugin Action Links (Left Side)
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="admin.php?page=order-shield">' . __('Settings', 'order-shield') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
