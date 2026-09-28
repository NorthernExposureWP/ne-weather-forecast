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

        return $this->weatherClient->getCurrentWeather(
            $location['latitude'],
            $location['longitude']
        );
    }
}