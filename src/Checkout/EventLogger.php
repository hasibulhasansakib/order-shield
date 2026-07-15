<?php
namespace OrderShield\Checkout;

use OrderShield\GeoLocation\GeoLocationProviderInterface;

class EventLogger {
    
    private GeoLocationProviderInterface $geoProvider;

    public function __construct(GeoLocationProviderInterface $geoProvider) {
        $this->geoProvider = $geoProvider;
    }

    /**
     * Log a checkout attempt.
     *
     * @param string $status 'success' or 'blocked'
     * @param array $customer_data ['phone' => '', 'email' => '']
     * @param int|null $rule_id ID of the rule that blocked it (if any)
     * @param string|null $cart_data JSON string containing cart snapshot
     */
    public function logAttempt(string $status, array $customer_data, ?int $rule_id = null, ?string $cart_data = null): void {
        global $wpdb;

        $ip_address = $this->getClientIp();
        $location = $this->geoProvider->getLocation($ip_address);

        $table = $wpdb->prefix . 'os_fraud_logs';
        
        $wpdb->insert(
            $table,
            [
                'ip_address'   => $ip_address,
                'phone_number' => sanitize_text_field($customer_data['phone'] ?? ''),
                'email_address'=> sanitize_email($customer_data['email'] ?? ''),
                'city'         => $location['city'] ?? null,
                'region'       => $location['region'] ?? null,
                'zip'          => $location['zip'] ?? null,
                'country'      => $location['country'] ?? null,
                'lat'          => $location['lat'] ?? null,
                'lon'          => $location['lon'] ?? null,
                'isp'          => $location['isp'] ?? null,
                'cart_data'    => $cart_data,
                'status'       => $status,
                'rule_id'      => $rule_id
            ],
            [
                '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d'
            ]
        );
    }

    /**
     * Get the real IP address of the client.
     */
    private function getClientIp(): string {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        return trim($ip);
    }
}
