<?php
namespace OrderShield\GeoLocation;

class IpApiProvider implements GeoLocationProviderInterface {
    
    private string $endpoint = 'http://ip-api.com/json/';

    /**
     * Get location data using IP-API.com
     *
     * @param string $ip_address The IP address to lookup.
     * @return array|null
     */
    public function getLocation(string $ip_address): ?array {
        // Skip local IP addresses for testing
        if (in_array($ip_address, ['127.0.0.1', '::1'])) {
            return [
                'city'    => 'Localhost',
                'country' => 'Local Network',
                'isp'     => 'Local ISP'
            ];
        }

        $url = $this->endpoint . $ip_address . '?fields=city,country,isp,status';
        
        $response = wp_remote_get($url, [
            'timeout' => 5,
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        if (is_wp_error($response)) {
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data || !isset($data['status']) || $data['status'] !== 'success') {
            return null;
        }

        return [
            'city'    => sanitize_text_field($data['city'] ?? ''),
            'country' => sanitize_text_field($data['country'] ?? ''),
            'isp'     => sanitize_text_field($data['isp'] ?? '')
        ];
    }
}
