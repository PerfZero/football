<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Football_Data_REST
{
    public function __construct(
        private Football_Data_Settings $settings,
        private Football_Data_Mock_Repository $mock,
        private Football_Data_Api_Client $api
    ) {
    }

    public function register_routes(): void
    {
        register_rest_route('football-data/v1', '/status', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'status'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('football-data/v1', '/mock/(?P<type>[a-z_-]+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'mock'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('football-data/v1', '/api/leagues/selected', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'selected_leagues'],
            'permission_callback' => fn() => current_user_can('manage_options'),
        ]);
    }

    public function status(): WP_REST_Response
    {
        return rest_ensure_response([
            'plugin' => 'Football Data',
            'version' => FOOTBALL_DATA_VERSION,
            'source' => $this->settings->is_mock_mode() ? 'mock' : 'api',
            'apiKeyConfigured' => $this->settings->api_key_configured(),
            'selectedLeagues' => $this->settings->selected_league_ids(),
            'selectedLeaguesCount' => count($this->settings->selected_league_ids()),
            'mockTypes' => $this->mock->types(),
        ]);
    }

    public function mock(WP_REST_Request $request): WP_REST_Response
    {
        $type = sanitize_key($request->get_param('type'));

        return rest_ensure_response([
            'type' => $type,
            'items' => $this->mock->all($type),
        ]);
    }

    public function selected_leagues(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $season = sanitize_text_field((string) ($request->get_param('season') ?: $this->settings->get()['default_season']));
        $items = $this->api->selected_leagues($season);

        if (is_wp_error($items)) {
            return $items;
        }

        return rest_ensure_response([
            'season' => $season,
            'count' => count($items),
            'items' => $items,
        ]);
    }
}
