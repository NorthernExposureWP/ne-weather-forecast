<?php

namespace NE\Weather\Frontend;

use NE\Weather\Service\WeatherService;

final readonly class WeatherShortcode
{
    public function __construct(
        private WeatherService $weatherService,
    ) {
    }

    public function register(): void
    {
        add_shortcode('ne_weather', [$this, 'render']);
    }

    public function render(): string
    {
        $weather = $this->weatherService->getWeatherForVisitor();
        $condition = $weather['condition'];
        $updatedAt = new \DateTimeImmutable($weather['time']);

        return sprintf(
            '<div class="ne-weather">
            <div>%s %s</div>
            <div>Temperature: %s °C</div>
            <div>Humidity: %s %%</div>
            <div>Feels like: %s °C</div>
            <div>Wind: %s km/h</div>
            <div>Weather at %s</div>
        </div>',
            esc_html($condition->icon()),
            esc_html($condition->label()),
            esc_html($weather['temperature']),
            esc_html($weather['humidity']),
            esc_html($weather['apparent_temperature']),
            esc_html($weather['wind_speed']),
            esc_html($updatedAt->format('H:i')),
        );
    }
}