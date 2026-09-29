<?php

namespace NE\Weather\Api;

final class GeoIpClient implements GeoIpClientInterface
{
    private const API_URL = 'https://ipapi.co';

    public function getLocationByIp(string $ip): array
    {
        $url = sprintf(
            '%s/%s/json/',
            self::API_URL,
            rawurlencode($ip)
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

        if (!empty($data['error'])) {
            throw new \RuntimeException(
                $data['reason'] ?? 'Unable to determine location'
            );
        }

        if (
            !isset($data['latitude'], $data['longitude'])
        ) {
            throw new \RuntimeException(
                'GeoIP response does not contain coordinates'
            );
        }

        return [
            'latitude' => (float) $data['latitude'],
            'longitude' => (float) $data['longitude'],
        ];
    }
}