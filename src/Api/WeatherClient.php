<?php

namespace NE\Weather\Api;

final class WeatherClient
{
    private const API_URL = 'https://api.open-meteo.com/v1/forecast';

    public function getCurrentWeather(
        float $latitude,
        float $longitude
    ): array {
        $url = add_query_arg(
            [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => implode(',', [
                    'temperature_2m',
                    'relative_humidity_2m',
                    'apparent_temperature',
                    'weather_code',
                    'wind_speed_10m',
                    'is_day',
                ]),
            ],
            self::API_URL
        );

        $response = wp_remote_get(
            $url,
            [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'timeout' => 5,
            ]
        );

        if (is_wp_error($response)) {
            throw new \RuntimeException(
                $response->get_error_message()
            );
        }

        $statusCode = wp_remote_retrieve_response_code($response);

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \RuntimeException(
                sprintf(
                    'Unexpected HTTP status: %d',
                    $statusCode
                )
            );
        }

        $body = wp_remote_retrieve_body($response);

        $data = json_decode(
            $body,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($data)) {
            throw new \RuntimeException(
                'Expected JSON object'
            );
        }

        if (
            !isset(
                $data['current']['time'],
                $data['current']['temperature_2m'],
                $data['current']['relative_humidity_2m'],
                $data['current']['apparent_temperature'],
                $data['current']['weather_code'],
                $data['current']['wind_speed_10m'],
                $data['current']['is_day']
            )
        ) {
            throw new \RuntimeException(
                'Weather response does not contain required data'
            );
        }

        $current = $data['current'];

        return [
            'time' => $current['time'],
            'temperature' => (float) $current['temperature_2m'],
            'humidity' => (int) $current['relative_humidity_2m'],
            'apparent_temperature' => (float) $current['apparent_temperature'],
            'weather_code' => (int) $current['weather_code'],
            'wind_speed' => (float) $current['wind_speed_10m'],
            'is_day' => (bool) $current['is_day'],
        ];
    }
}