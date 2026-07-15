<?php
namespace OrderShield\Admin;

class DashboardController {

    public function init(): void {
        add_action('wp_ajax_os_get_stats', [$this, 'getStats']);
        add_action('wp_ajax_os_get_logs', [$this, 'getLogs']);
        add_action('wp_ajax_os_get_rules', [$this, 'getRules']);
        add_action('wp_ajax_os_add_rule', [$this, 'addRule']);
        add_action('wp_ajax_os_delete_rule', [$this, 'deleteRule']);
        add_action('wp_ajax_os_get_settings', [$this, 'getSettings']);
        add_action('wp_ajax_os_save_settings', [$this, 'saveSettings']);
        add_action('wp_ajax_os_clear_logs', [$this, 'clearLogs']);
    }

    private function verifyNonce(): void {
        if (!check_ajax_referer('os_admin_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid security token.');
        }
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Unauthorized.');
        }
    }

    public function getStats(): void {
        $this->verifyNonce();
        global $wpdb;
        $logs_table = $wpdb->prefix . 'os_fraud_logs';

        $filter = isset($_POST['date_filter']) ? sanitize_text_field($_POST['date_filter']) : 'today';
        $date_query = $this->getDateQueryCondition($filter);

        $total_attempts = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table WHERE $date_query");
        $blocked_attempts = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table WHERE status = 'blocked' AND $date_query");
        $success_attempts = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table WHERE status = 'success' AND $date_query");

        wp_send_json_success([
            'total_today' => (int) $total_attempts,
            'blocked_today' => (int) $blocked_attempts,
            'success_today' => (int) $success_attempts
        ]);
    }

    public function getLogs(): void {
        $this->verifyNonce();
        global $wpdb;
        $logs_table = $wpdb->prefix . 'os_fraud_logs';

        $page = isset($_POST['page']) ? (int) $_POST['page'] : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $filter = isset($_POST['date_filter']) ? sanitize_text_field($_POST['date_filter']) : 'today';
        $date_query = $this->getDateQueryCondition($filter);

        $results = $wpdb->get_results("
            SELECT * FROM $logs_table 
            WHERE $date_query
            ORDER BY created_at DESC 
            LIMIT $per_page OFFSET $offset
        ", ARRAY_A);

        wp_send_json_success([
            'logs' => $results
        ]);
    }

    private function getDateQueryCondition(string $filter): string {
        switch ($filter) {
            case 'today':
                return "created_at >= '" . date('Y-m-d 00:00:00') . "'";
            case 'yesterday':
                return "created_at >= '" . date('Y-m-d 00:00:00', strtotime('-1 day')) . "' AND created_at <= '" . date('Y-m-d 23:59:59', strtotime('-1 day')) . "'";
            case 'last_7_days':
                return "created_at >= '" . date('Y-m-d 00:00:00', strtotime('-7 days')) . "'";
            case 'this_month':
                return "created_at >= '" . date('Y-m-01 00:00:00') . "'";
            case 'all_time':
            default:
                return "1=1";
        }
    }

    public function getRules(): void {
        $this->verifyNonce();
        global $wpdb;
        $rules_table = $wpdb->prefix . 'os_rules';

        $results = $wpdb->get_results("SELECT * FROM $rules_table ORDER BY created_at DESC", ARRAY_A);
        wp_send_json_success(['rules' => $results]);
    }

    public function addRule(): void {
        $this->verifyNonce();
        global $wpdb;
        $rules_table = $wpdb->prefix . 'os_rules';

        $wpdb->insert($rules_table, [
            'rule_type'    => sanitize_text_field($_POST['rule_type']),
            'target_type'  => sanitize_text_field($_POST['target_type']),
            'target_value' => sanitize_text_field($_POST['target_value']),
            'reason'       => sanitize_textarea_field($_POST['reason'])
        ]);

        wp_send_json_success();
    }

    public function deleteRule(): void {
        $this->verifyNonce();
        global $wpdb;
        $rules_table = $wpdb->prefix . 'os_rules';
        
        $wpdb->delete($rules_table, ['id' => (int) $_POST['id']]);
        wp_send_json_success();
    }

    public function getSettings(): void {
        $this->verifyNonce();
        wp_send_json_success([
            'os_max_orders_per_day' => get_option('os_max_orders_per_day', 3),
            'os_block_msg'          => get_option('os_block_msg', 'You have been blocked from placing orders. Please contact support.'),
            'os_limit_msg'          => get_option('os_limit_msg', 'You have exceeded the maximum number of orders allowed per day.'),
            'os_fake_phone'         => get_option('os_fake_phone_detection', 'yes')
        ]);
    }

    public function saveSettings(): void {
        $this->verifyNonce();
        
        update_option('os_max_orders_per_day', (int) $_POST['os_max_orders_per_day']);
        update_option('os_block_msg', sanitize_textarea_field($_POST['os_block_msg']));
        update_option('os_limit_msg', sanitize_textarea_field($_POST['os_limit_msg']));
        update_option('os_fake_phone_detection', sanitize_text_field($_POST['os_fake_phone']));
        
        wp_send_json_success();
    }

    public function clearLogs(): void {
        $this->verifyNonce();
        global $wpdb;
        $logs_table = $wpdb->prefix . 'os_fraud_logs';
        
        $date = sanitize_text_field($_POST['date']);
        if (empty($date)) {
            wp_send_json_error('Invalid date provided.');
        }

        // Validate date format YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            wp_send_json_error('Invalid date format. Use YYYY-MM-DD.');
        }

        // Delete logs before this date
        $result = $wpdb->query(
            $wpdb->prepare("DELETE FROM $logs_table WHERE created_at < %s", $date . ' 00:00:00')
        );

        if ($result !== false) {
            wp_send_json_success(['deleted' => $result]);
        } else {
            wp_send_json_error('Failed to clear logs.');
        }
    }
}
