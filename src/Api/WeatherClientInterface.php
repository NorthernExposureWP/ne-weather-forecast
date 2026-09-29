<?php

namespace NE\Weather\Api;

interface WeatherClientInterface
{
    public function getCurrentWeather(
        float $latitude,
        float $longitude
    ): array;
}