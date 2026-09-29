<?php

namespace NE\Weather\Service;

use NE\Weather\Api\WeatherClientInterface;
use NE\Weather\Api\GeoIpClientInterface;

final readonly class WeatherService
{
    public function __construct(
        private GeoIpClientInterface $geoIpClient,
        private WeatherClientInterface $weatherClient,
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
            error_log(
                sprintf(
                    'NE Weather: Cache hit. Coordinates: %.2f, %.2f',
                    $location['latitude'],
                    $location['longitude']
                )
            );

            return $cachedWeather;
        }

        try {
            $weather = $this->weatherClient->getCurrentWeather(
                $location['latitude'],
                $location['longitude']
            );

            error_log(
                sprintf(
                    'NE Weather: Weather retrieved successfully. Coordinates: %.2f, %.2f',
                    $location['latitude'],
                    $location['longitude']
                )
            );
        } catch (\Throwable $e) {
            error_log(
                sprintf(
                    'NE Weather: Failed to retrieve weather: %s',
                    $e->getMessage()
                )
            );

            throw $e;
        }

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