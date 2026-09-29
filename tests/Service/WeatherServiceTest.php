<?php

namespace Tests\Service;

use NE\Weather\Api\GeoIpClient;
use NE\Weather\Api\GeoIpClientInterface;
use NE\Weather\Api\WeatherClientInterface;
use NE\Weather\Service\WeatherService;
use NE\Weather\Domain\WeatherCondition;
use PHPUnit\Framework\TestCase;

final class WeatherServiceTest extends TestCase
{
    public function testReturnsCachedWeather(): void
    {
        $geoIpClient = new GeoIpClient();

        $weatherClient = $this->createMock(WeatherClientInterface::class);

        $weatherClient
            ->expects($this->never())
            ->method('getCurrentWeather');

        $service = new WeatherService(
            $geoIpClient,
            $weatherClient
        );

        $_SERVER['REMOTE_ADDR'] = '8.8.8.8';

        $cachedWeather = [
            'latitude' => 37.42,
            'longitude' => -122.08,
            'time' => '2026-09-28T13:30',
            'temperature' => 20.0,
            'humidity' => 49,
            'apparent_temperature' => 18.5,
            'condition' => WeatherCondition::PARTLY_CLOUDY,
            'wind_speed' => 11.5,
            'is_day' => true,
        ];

        set_transient(
            'ne_weather_cache_37.42_-122.08',
            $cachedWeather,
            300
        );

        $result = $service->getWeatherForVisitor();

        $this->assertSame(
            $cachedWeather,
            $result
        );

        delete_transient('ne_weather_cache_37.42_-122.08');
    }

    public function testFetchesAndCachesWeatherOnCacheMiss(): void
    {
        $geoIpClient = $this->createMock(GeoIpClientInterface::class);

        $geoIpClient
            ->expects($this->once())
            ->method('getLocationByIp')
            ->with('8.8.8.8')
            ->willReturn([
                'latitude' => 37.42301,
                'longitude' => -122.083352,
            ]);

        $weatherClient = $this->createMock(WeatherClientInterface::class);

        $expectedWeather = [
            'latitude' => 37.42,
            'longitude' => -122.08,
            'time' => '2026-09-28T13:30',
            'temperature' => 20.0,
            'humidity' => 49,
            'apparent_temperature' => 18.5,
            'condition' => WeatherCondition::PARTLY_CLOUDY,
            'wind_speed' => 11.5,
            'is_day' => true,
        ];

        $weatherClient
            ->expects($this->once())
            ->method('getCurrentWeather')
            ->with(37.42301, -122.083352)
            ->willReturn($expectedWeather);


        $service = new WeatherService(
            $geoIpClient,
            $weatherClient
        );

        $_SERVER['REMOTE_ADDR'] = '8.8.8.8';

        $result = $service->getWeatherForVisitor();

        $this->assertSame(
            $expectedWeather,
            $result
        );

        $this->assertSame(
            $expectedWeather,
            get_transient('ne_weather_cache_37.42_-122.08')
        );

        delete_transient('ne_weather_cache_37.42_-122.08');
    }
}