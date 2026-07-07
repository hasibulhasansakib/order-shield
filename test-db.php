<?php
require_once dirname(__DIR__, 3) . '/wp-load.php';
global $wpdb;

$logs_table = $wpdb->prefix . 'os_fraud_logs';
$rules_table = $wpdb->prefix . 'os_rules';

echo "Logs Table: $logs_table\n";
$logs = $wpdb->get_results("SELECT * FROM $logs_table");
print_r($logs);

echo "\nRules Table: $rules_table\n";
$rules = $wpdb->get_results("SELECT * FROM $rules_table");
print_r($rules);
