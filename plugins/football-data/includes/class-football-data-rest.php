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
    }

    public function status(): WP_REST_Response
    {
        return rest_ensure_response([
            'plugin' => 'Football Data',
            'version' => FOOTBALL_DATA_VERSION,
            'source' => $this->settings->is_mock_mode() ? 'mock' : 'api',
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
}
