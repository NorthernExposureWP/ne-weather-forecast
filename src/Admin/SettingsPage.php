<?php

namespace NE\Weather\Admin;

final class SettingsPage
{
    public function register(): void
    {
        add_action(
            'admin_menu',
            [$this, 'addPage']
        );
        add_action(
            'admin_init',
            [$this, 'registerSettings']
        );

    }

    public function addPage(): void
    {
        add_options_page(
            'Weather Forecast',
            'Weather Forecast',
            'manage_options',
            'ne-weather-forecast',
            [$this, 'render']
        );
    }

    public function render(): void
    {
        ?>
        <div class="wrap">
            <h1>Weather Forecast</h1>

            <form method="post" action="options.php">
                <?php
                settings_fields('ne_weather_settings');
                do_settings_sections('ne-weather-forecast');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function registerSettings(): void
    {
        register_setting(
            'ne_weather_settings',
            'ne_weather_fallback_latitude'
        );

        register_setting(
            'ne_weather_settings',
            'ne_weather_fallback_longitude'
        );

        register_setting(
                'ne_weather_settings',
                'ne_weather_cache_ttl',
                [
                        'type' => 'integer',
                        'sanitize_callback' => function (int $value): int {
                            return min(1800, max(300, $value));
                        },
                        'default' => 900,
                ]
        );

        add_settings_section(
                'ne_weather_general',
                'General Settings',
                [$this, 'renderGeneralSection'],
                'ne-weather-forecast'
        );

        add_settings_field(
                'ne_weather_fallback_latitude',
                'Fallback latitude',
                [$this, 'renderFallbackLatitudeField'],
                'ne-weather-forecast',
                'ne_weather_general'
        );

        add_settings_field(
                'ne_weather_fallback_longitude',
                'Fallback longitude',
                [$this, 'renderFallbackLongitudeField'],
                'ne-weather-forecast',
                'ne_weather_general'
        );

        add_settings_field(
                'ne_weather_cache_ttl',
                'Cache TTL',
                [$this, 'renderCacheTtlField'],
                'ne-weather-forecast',
                'ne_weather_general'
        );
    }

    public function renderGeneralSection(): void
    {
        echo '<p>Configure the default weather location.</p>';
    }

    public function renderFallbackLongitudeField(): void
    {
        $value = get_option('ne_weather_fallback_longitude', '');
        ?>
        <input
                type="number"
                name="ne_weather_fallback_longitude"
                value="<?= esc_attr($value) ?>"
                step="any"
                class="regular-text"
        >
        <p class="description">
            Longitude used when visitor location cannot be detected.
        </p>
        <?php
    }

    public function renderFallbackLatitudeField(): void
    {
        $value = get_option('ne_weather_fallback_latitude', '');
        ?>
        <input
                type="number"
                name="ne_weather_fallback_latitude"
                value="<?= esc_attr($value) ?>"
                step="any"
                class="regular-text"
        >
        <p class="description">
            Latitude used when visitor location cannot be detected.
        </p>
        <?php
    }

    public function renderCacheTtlField(): void
    {
        $value = get_option('ne_weather_cache_ttl', '900');
        ?>
        <input
                type="number"
                name="ne_weather_cache_ttl"
                value="<?= esc_attr($value) ?>"
                min="300"
                max="1800"
                step="60"
                class="small-text"
        >
        <p class="description">
            How long weather data should be cached, in seconds.
        </p>
        <?php
    }
}