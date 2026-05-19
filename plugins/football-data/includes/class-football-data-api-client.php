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
        $api_key = $this->settings->api_key();

        if ($api_key === '') {
            return new WP_Error('football_data_no_api_key', 'API key не задан. Включите мок-режим или добавьте ключ в настройках.');
        }

        $url = trailingslashit($settings['api_host']) . ltrim($endpoint, '/');
        if ($params) {
            $url = add_query_arg($params, $url);
        }

        $response = wp_remote_get($url, [
            'timeout' => 20,
            'headers' => [
                'x-apisports-key' => $api_key,
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

    public function request_cached(string $endpoint, array $params = [], ?int $ttl = null): array|WP_Error
    {
        ksort($params);

        $cache_key = 'football_data_api_' . md5($endpoint . ':' . wp_json_encode($params));
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            return $cached;
        }

        $response = $this->request($endpoint, $params);
        if (!is_wp_error($response)) {
            set_transient($cache_key, $response, $ttl ?? $this->settings->api_cache_ttl());
        }

        return $response;
    }

    public function request_for_league(string $endpoint, int $league_id, array $params = []): array|WP_Error
    {
        if (!$this->is_league_allowed($league_id)) {
            return new WP_Error(
                'football_data_league_not_allowed',
                'Лига не выбрана в настройках Football Data.',
                ['league_id' => $league_id]
            );
        }

        return $this->request_cached($endpoint, array_merge($params, [
            'league' => $league_id,
        ]));
    }

    public function selected_leagues(?string $season = null): array|WP_Error
    {
        $league_ids = $this->settings->selected_league_ids();
        if (!$league_ids) {
            return new WP_Error('football_data_no_selected_leagues', 'В настройках не выбраны лиги для загрузки.');
        }

        $items = [];
        foreach ($league_ids as $league_id) {
            $params = ['id' => $league_id];
            if ($season !== null && $season !== '') {
                $params['season'] = $season;
            }

            $response = $this->request_cached('leagues', $params);
            if (is_wp_error($response)) {
                return $response;
            }

            foreach ($response['response'] ?? [] as $item) {
                $items[] = $item;
            }
        }

        return $items;
    }

    public function is_league_allowed(int $league_id): bool
    {
        return in_array($league_id, $this->settings->selected_league_ids(), true);
    }
}
