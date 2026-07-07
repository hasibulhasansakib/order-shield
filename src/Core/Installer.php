<?php
namespace OrderShield\Core;

class Installer {
    
    /**
     * Run upon plugin activation.
     */
    public static function activate(): void {
        self::createDatabaseTables();
        flush_rewrite_rules();
    }

    /**
     * Run upon plugin deactivation.
     */
    public static function deactivate(): void {
        flush_rewrite_rules();
    }

    /**
     * Create custom database tables for high-performance logging and rules.
     */
    private static function createDatabaseTables(): void {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $logs_table = $wpdb->prefix . 'os_fraud_logs';
        $rules_table = $wpdb->prefix . 'os_rules';

        $sql = "CREATE TABLE $logs_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            ip_address varchar(45) NOT NULL,
            phone_number varchar(20) DEFAULT NULL,
            email_address varchar(100) DEFAULT NULL,
            city varchar(100) DEFAULT NULL,
            region varchar(100) DEFAULT NULL,
            zip varchar(50) DEFAULT NULL,
            country varchar(100) DEFAULT NULL,
            lat varchar(50) DEFAULT NULL,
            lon varchar(50) DEFAULT NULL,
            isp varchar(255) DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'success',
            rule_id bigint(20) unsigned DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY ip_address (ip_address),
            KEY phone_number (phone_number),
            KEY created_at (created_at)
        ) $charset_collate;

        CREATE TABLE $rules_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            rule_type varchar(20) NOT NULL, 
            target_type varchar(20) NOT NULL,
            target_value varchar(255) NOT NULL,
            reason text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY target (rule_type, target_type, target_value)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
