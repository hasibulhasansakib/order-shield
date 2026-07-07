<?php
namespace OrderShield\GeoLocation;

interface GeoLocationProviderInterface {
    
    /**
     * Get location data for a given IP address.
     *
     * @param string $ip_address The IP address to lookup.
     * @return array|null Returns an array with ['city', 'country', 'isp'] or null on failure.
     */
    public function getLocation(string $ip_address): ?array;
}
