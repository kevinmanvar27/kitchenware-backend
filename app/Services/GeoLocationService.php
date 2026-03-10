<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeoLocationService
{
    /**
     * Get location data from IP address
     * Uses ip-api.com (free, no API key required, 45 requests/minute limit)
     *
     * @param string|null $ip
     * @return array
     */
    public static function getLocation(?string $ip): array
    {
        $default = [
            'country' => null,
            'country_code' => null,
            'region' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
        ];

        if (!$ip || $ip === '127.0.0.1' || $ip === '::1' || self::isPrivateIP($ip)) {
            return array_merge($default, ['country' => 'Local', 'city' => 'Localhost']);
        }

        // Cache location data for 24 hours to reduce API calls
        $cacheKey = 'geo_location_' . md5($ip);
        
        return Cache::remember($cacheKey, 86400, function () use ($ip, $default) {
            try {
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'status,country,countryCode,regionName,city,lat,lon'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['status']) && $data['status'] === 'success') {
                        return [
                            'country' => $data['country'] ?? null,
                            'country_code' => $data['countryCode'] ?? null,
                            'region' => $data['regionName'] ?? null,
                            'city' => $data['city'] ?? null,
                            'latitude' => $data['lat'] ?? null,
                            'longitude' => $data['lon'] ?? null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::debug('GeoLocation lookup failed: ' . $e->getMessage());
            }

            return $default;
        });
    }

    /**
     * Check if IP is a private/local IP
     *
     * @param string $ip
     * @return bool
     */
    private static function isPrivateIP(string $ip): bool
    {
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
