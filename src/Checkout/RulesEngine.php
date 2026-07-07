<?php
namespace OrderShield\Checkout;

class RulesEngine {
    
    /**
     * Check if a customer is allowed to place an order.
     *
     * @param array $customer_data ['phone' => '', 'email' => '', 'ip' => '']
     * @return array ['allowed' => true/false, 'reason' => '', 'rule_id' => null]
     */
    public function evaluate(array $customer_data): array {
        global $wpdb;
        $rules_table = $wpdb->prefix . 'os_rules';

        $phone = $customer_data['phone'] ?? '';
        $email = $customer_data['email'] ?? '';
        $ip    = $customer_data['ip'] ?? '';

        // 1. Check Whitelist (Whitelist overrides everything)
        $whitelist_query = $wpdb->prepare("SELECT id FROM $rules_table WHERE rule_type = 'whitelist' AND 
            ((target_type = 'phone' AND target_value = %s) OR 
             (target_type = 'email' AND target_value = %s) OR 
             (target_type = 'ip' AND target_value = %s)) LIMIT 1", $phone, $email, $ip);
        
        $whitelist = $wpdb->get_row($whitelist_query);
        if ($whitelist) {
            return ['allowed' => true, 'reason' => 'whitelisted', 'rule_id' => (int) $whitelist->id];
        }

        // 2. Check Blacklist
        $blacklist_query = $wpdb->prepare("SELECT id, reason FROM $rules_table WHERE rule_type = 'blacklist' AND 
            ((target_type = 'phone' AND target_value = %s) OR 
             (target_type = 'email' AND target_value = %s) OR 
             (target_type = 'ip' AND target_value = %s)) LIMIT 1", $phone, $email, $ip);
        
        $blacklist = $wpdb->get_row($blacklist_query);
        if ($blacklist) {
            $block_msg = get_option('os_block_msg', 'You have been blocked from placing orders. Please contact support.');
            return [
                'allowed' => false, 
                'reason' => $block_msg, 
                'rule_id' => (int) $blacklist->id
            ];
        }

        // 3. Check Daily Limits
        $limit_exceeded = $this->checkDailyLimits($customer_data);
        if ($limit_exceeded) {
            $limit_msg = get_option('os_limit_msg', 'You have exceeded the maximum number of orders allowed per day.');
            return [
                'allowed' => false, 
                'reason' => $limit_msg, 
                'rule_id' => null // Built-in limit rule
            ];
        }

        // All checks passed
        return ['allowed' => true, 'reason' => '', 'rule_id' => null];
    }

    /**
     * Check if the user has exceeded their daily limit.
     */
    private function checkDailyLimits(array $customer_data): bool {
        global $wpdb;
        $logs_table = $wpdb->prefix . 'os_fraud_logs';

        // Get limits from settings (mocking for now, will implement settings later)
        $max_orders_per_day = (int) get_option('os_max_orders_per_day', 3);

        $phone = $customer_data['phone'] ?? '';
        $email = $customer_data['email'] ?? '';
        $ip    = $customer_data['ip'] ?? '';

        $today = date('Y-m-d 00:00:00');

        $query = $wpdb->prepare("
            SELECT COUNT(id) FROM $logs_table 
            WHERE status = 'success' AND created_at >= %s AND 
            (ip_address = %s OR phone_number = %s OR email_address = %s)
        ", $today, $ip, $phone, $email);

        $order_count = (int) $wpdb->get_var($query);

        return $order_count >= $max_orders_per_day;
    }
}
