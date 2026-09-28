<?php

namespace NE\Weather\Domain;

enum WeatherCondition: string
{
    case CLEAR = 'clear';
    case MAINLY_CLEAR = 'mainly_clear';
    case PARTLY_CLOUDY = 'partly_cloudy';
    case OVERCAST = 'overcast';
    case FOG = 'fog';
    case DRIZZLE = 'drizzle';
    case RAIN = 'rain';
    case SNOW = 'snow';
    case RAIN_SHOWERS = 'rain_showers';
    case SNOW_SHOWERS = 'snow_showers';
    case THUNDERSTORM = 'thunderstorm';

    public function label(): string
    {
        return match ($this) {
            self::CLEAR => 'Clear sky',
            self::MAINLY_CLEAR => 'Mainly clear',
            self::PARTLY_CLOUDY => 'Partly cloudy',
            self::OVERCAST => 'Overcast',
            self::FOG => 'Fog',
            self::DRIZZLE => 'Drizzle',
            self::RAIN => 'Rain',
            self::SNOW => 'Snow',
            self::RAIN_SHOWERS => 'Rain showers',
            self::SNOW_SHOWERS => 'Snow showers',
            self::THUNDERSTORM => 'Thunderstorm',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CLEAR => '☀️',
            self::MAINLY_CLEAR => '🌤️',
            self::PARTLY_CLOUDY => '⛅',
            self::OVERCAST => '☁️',
            self::FOG => '🌫️',
            self::DRIZZLE => '🌦️',
            self::RAIN => '🌧️',
            self::SNOW => '❄️',
            self::RAIN_SHOWERS => '🌦️',
            self::SNOW_SHOWERS => '🌨️',
            self::THUNDERSTORM => '⛈️',
        };
    }
}