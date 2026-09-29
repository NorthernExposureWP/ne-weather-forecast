<?php

namespace NE\Weather\Frontend;

use WP_REST_Request;
use WP_REST_Server;

class WebhookController
{
    public static function init(): void
    {
        add_action('rest_api_init', self::registerRoutes(...));
    }

    public static function registerRoutes(): void
    {
        register_rest_route(
            'ne-weather/v1',
            '/webhook',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => self::getWeather(...),
            ]
        );
    }

    public static function getWeather(WP_REST_Request $request): array|\WP_Error
    {
        $secret = $request->get_header('X-Webhook-Secret');

        if ($secret !== NE_WEATHER_WEBHOOK_SECRET) {
            return new \WP_Error(
                'invalid_secret',
                'Invalid webhook secret.',
                ['status' => 401]
            );
        }

        $data = $request->get_json_params();

        if (
            !isset($data['event']) ||
            !is_string($data['event']) ||
            $data['event'] !== 'weather.updated'
        ) {
            return new \WP_Error(
                'invalid_event',
                'Invalid or missing event.',
                ['status' => 400]
            );
        }

        if (
            !isset($data['event_id']) ||
            !is_string($data['event_id']) ||
            $data['event_id'] === ''
        ) {
            return new \WP_Error(
                'invalid_event_id',
                'Invalid or missing event ID.',
                ['status' => 400]
            );
        }

        $transient_key = 'ne_weather_webhook_' . $data['event_id'];

        if (get_transient($transient_key) !== false) {
            return [
                'received' => true,
                'duplicate' => true,
            ];
        }

        set_transient($transient_key, true, HOUR_IN_SECONDS);

        return [
            'received' => true,
            'duplicate' => false,
        ];
    }
}