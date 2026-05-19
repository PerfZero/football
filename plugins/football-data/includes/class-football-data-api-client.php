<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Football_Data_Api_Client
{
    public function __construct(private Football_Data_Settings $settings)
    {
    }

    public function request(string $endpoint, array $params = []): array|WP_Error
    {
        $settings = $this->settings->get();

        if (empty($settings['api_key'])) {
            return new WP_Error('football_data_no_api_key', 'API key не задан. Включите мок-режим или добавьте ключ в настройках.');
        }

        $url = trailingslashit($settings['api_host']) . ltrim($endpoint, '/');
        if ($params) {
            $url = add_query_arg($params, $url);
        }

        $response = wp_remote_get($url, [
            'timeout' => 20,
            'headers' => [
                'x-apisports-key' => $settings['api_key'],
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $status = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($status < 200 || $status >= 300) {
            return new WP_Error('football_data_api_error', 'API-Football вернул ошибку.', [
                'status' => $status,
                'body' => $body,
            ]);
        }

        return is_array($body) ? $body : [];
    }
}
