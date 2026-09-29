<?php

namespace Tests\Api;

use NE\Weather\Api\WeatherClient;
use NE\Weather\Domain\WeatherCondition;
use PHPUnit\Framework\TestCase;

final class WeatherClientTest extends TestCase
{
    public function testFetchesCurrentWeather(): void
    {
        add_filter(
            'pre_http_request',
            function () {
                return [
                    'headers' => [],
                    'body' => json_encode([
                        'latitude' => 50.45,
                        'longitude' => 30.52,
                        'current' => [
                            'time' => '2026-09-28T13:30',
                            'temperature_2m' => 20.0,
                            'relative_humidity_2m' => 49,
                            'apparent_temperature' => 18.5,
                            'weather_code' => 2,
                            'wind_speed_10m' => 11.5,
                            'is_day' => 1,
                        ],
                    ]),
                    'response' => [
                        'code' => 200,
                    ],
                    'cookies' => [],
                    'filename' => null,
                ];
            }
        );

        $client = new WeatherClient();
        $result = $client->getCurrentWeather(50.45, 30.52);

        remove_all_filters('pre_http_request');

        $this->assertSame(50.45, $result['latitude']);
        $this->assertSame(30.52, $result['longitude']);
        $this->assertSame(20.0, $result['temperature']);
        $this->assertSame(49, $result['humidity']);
        $this->assertSame(18.5, $result['apparent_temperature']);
        $this->assertSame(11.5, $result['wind_speed']);
        $this->assertSame(WeatherCondition::PARTLY_CLOUDY, $result['condition']);
    }

    public function testThrowsExceptionOnHttpError(): void
    {
        $filter = function () {
            return [
                'headers' => [],
                'body' => '',
                'response' => [
                    'code' => 500,
                    'message' => 'Internal Server Error',
                ],
                'cookies' => [],
                'filename' => null,
            ];
        };

        add_filter('pre_http_request', $filter);

        $client = new WeatherClient();

        try {
            $client->getCurrentWeather(50.45, 30.52);

            $this->fail('Expected RuntimeException was not thrown.');
        } catch (\RuntimeException $e) {
            $this->assertSame(
                'Unexpected HTTP status: 500',
                $e->getMessage()
            );
        } finally {
            remove_filter('pre_http_request', $filter);
        }
    }

    public function testThrowsExceptionOnTransportError(): void
    {
        $filter = function () {
            return new \WP_Error(
                'http_request_failed',
                'Connection timed out'
            );
        };

        add_filter('pre_http_request', $filter);

        $client = new WeatherClient();

        try {
            $client->getCurrentWeather(50.45, 30.52);

            $this->fail('Expected RuntimeException was not thrown.');
        } catch (\RuntimeException $e) {
            $this->assertSame(
                'Connection timed out',
                $e->getMessage()
            );
        } finally {
            remove_filter('pre_http_request', $filter);
        }
    }

    public function testThrowsExceptionOnInvalidJson(): void
    {
        $filter = function () {
            return [
                'headers' => [],
                'body' => '{invalid json}',
                'response' => [
                    'code' => 200,
                    'message' => 'OK',
                ],
                'cookies' => [],
                'filename' => null,
            ];
        };

        add_filter('pre_http_request', $filter);

        $client = new WeatherClient();

        try {
            $client->getCurrentWeather(50.45, 30.52);

            $this->fail('Expected JsonException was not thrown.');
        } catch (\JsonException $e) {
            $this->assertStringContainsString(
                'Syntax error',
                $e->getMessage()
            );
        } finally {
            remove_filter('pre_http_request', $filter);
        }
    }

    public function testThrowsExceptionWhenWeatherResponseIsIncomplete(): void
    {
        $filter = function () {
            return [
                'headers' => [],
                'body' => json_encode([
                    'latitude' => 50.45,
                    'longitude' => 30.52,
                    'current' => [
                        'time' => '2026-09-28T13:30',
                        'temperature_2m' => 20.0,
                    ],
                ]),
                'response' => [
                    'code' => 200,
                    'message' => 'OK',
                ],
                'cookies' => [],
                'filename' => null,
            ];
        };

        add_filter('pre_http_request', $filter);

        $client = new WeatherClient();

        try {
            $client->getCurrentWeather(50.45, 30.52);

            $this->fail('Expected RuntimeException was not thrown.');
        } catch (\RuntimeException $e) {
            $this->assertSame(
                'Weather response does not contain required data',
                $e->getMessage()
            );
        } finally {
            remove_filter('pre_http_request', $filter);
        }
    }

    public function testThrowsExceptionOnUnknownWeatherCode(): void
    {
        $filter = function () {
            return [
                'headers' => [],
                'body' => json_encode([
                    'latitude' => 50.45,
                    'longitude' => 30.52,
                    'current' => [
                        'time' => '2026-09-28T13:30',
                        'temperature_2m' => 20.0,
                        'relative_humidity_2m' => 49,
                        'apparent_temperature' => 18.5,
                        'weather_code' => 999,
                        'wind_speed_10m' => 11.5,
                        'is_day' => 1,
                    ],
                ]),
                'response' => [
                    'code' => 200,
                    'message' => 'OK',
                ],
                'cookies' => [],
                'filename' => null,
            ];
        };

        add_filter('pre_http_request', $filter);

        $client = new WeatherClient();

        try {
            $client->getCurrentWeather(50.45, 30.52);

            $this->fail('Expected RuntimeException was not thrown.');
        } catch (\RuntimeException $e) {
            $this->assertSame(
                'Unknown weather code: 999',
                $e->getMessage()
            );
        } finally {
            remove_filter('pre_http_request', $filter);
        }
    }
}