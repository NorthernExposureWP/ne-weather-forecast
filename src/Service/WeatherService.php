<?php

namespace NE\Weather\Service;

use NE\Weather\Api\GeoIpClient;
use NE\Weather\Api\WeatherClient;

final class WeatherService
{
    public function __construct(
        private GeoIpClient $geoIpClient,
        private WeatherClient $weatherClient,
    ) {
    }

    public function getWeatherForVisitor(): array
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        try {
            if ($ip === '') {
                throw new \RuntimeException(
                    'Unable to determine visitor IP address'
                );
            }

            $location = $this->geoIpClient->getLocationByIp($ip);
        } catch (\Throwable) {
            $location = [
                'latitude' => (float) get_option(
                    'ne_weather_fallback_latitude'
                ),
                'longitude' => (float) get_option(
                    'ne_weather_fallback_longitude'
                ),
            ];
        }

        $cacheKey = $this->getCacheKey(
            $location['latitude'],
            $location['longitude']
        );

        $cachedWeather = get_transient($cacheKey);

        if ($cachedWeather !== false) {
            return $cachedWeather;
        }

        $weather = $this->weatherClient->getCurrentWeather(
            $location['latitude'],
            $location['longitude']
        );

        set_transient(
            $cacheKey,
            $weather,
            (int) get_option('ne_weather_cache_ttl', 900)
        );

        return $weather;
    }

    private function getCacheKey(
        float $latitude,
        float $longitude
    ): string {
        $latitude = round($latitude, 2);
        $longitude = round($longitude, 2);

        return sprintf(
            'ne_weather_cache_%s_%s',
            $latitude,
            $longitude
        );
    }
}