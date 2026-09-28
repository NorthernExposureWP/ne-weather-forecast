<?php

/**
 * Plugin Name: Northern Exposure Weather Forecast
 * Description: Displays current weather based on visitor's approximate location.
 * Version: 1.0.0
 * Author: Northern Exposure
 */

defined('ABSPATH') || exit;

require_once __DIR__ . '/src/Admin/SettingsPage.php';
require_once __DIR__ . '/src/Service/WeatherService.php';
require_once __DIR__ . '/src/Api/GeoIpClient.php';
require_once __DIR__ . '/src/Api/WeatherClient.php';

$settings_page = new \NE\Weather\Admin\SettingsPage();
$settings_page->register();

register_activation_hook(
    __FILE__,
    function (): void {
        if (get_option('ne_weather_fallback_latitude') === false) {
            add_option(
                'ne_weather_fallback_latitude',
                '50.4501'
            );
        }

        if (get_option('ne_weather_fallback_longitude') === false) {
            add_option(
                'ne_weather_fallback_longitude',
                '30.5234'
            );
        }

        if (get_option('ne_weather_cache_ttl') === false) {
            add_option(
                'ne_weather_cache_ttl',
                '900'
            );
        }
    }
);