<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Football_Data_Plugin
{
    private static ?self $instance = null;

    public Football_Data_Settings $settings;
    public Football_Data_CPT $cpt;
    public Football_Data_Api_Client $api;
    public Football_Data_Mock_Repository $mock;
    public Football_Data_REST $rest;
    public Football_Data_Shortcodes $shortcodes;
    public Football_Data_Carbon_Fields $carbon_fields;
    public Football_Data_I18n $i18n;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->settings = new Football_Data_Settings();
        $this->cpt = new Football_Data_CPT();
        $this->api = new Football_Data_Api_Client($this->settings);
        $this->mock = new Football_Data_Mock_Repository();
        $this->rest = new Football_Data_REST($this->settings, $this->mock, $this->api);
        $this->shortcodes = new Football_Data_Shortcodes($this->mock);
        $this->carbon_fields = new Football_Data_Carbon_Fields();
        $this->i18n = new Football_Data_I18n();

        add_action('init', [$this->cpt, 'register']);
        add_action('admin_menu', [$this->settings, 'register_admin_pages']);
        add_action('admin_init', [$this->settings, 'register_settings']);
        add_action('rest_api_init', [$this->rest, 'register_routes']);
        add_action('init', [$this->shortcodes, 'register']);
        add_action('after_setup_theme', [$this->carbon_fields, 'boot']);
        add_action('carbon_fields_register_fields', [$this->carbon_fields, 'register_fields']);
        add_action('admin_notices', [$this->carbon_fields, 'admin_notice']);
        add_action('init', [$this->i18n, 'register_strings']);
        add_filter('pll_get_post_types', [$this->cpt, 'polylang_post_types'], 10, 2);
        add_filter('pll_get_taxonomies', [$this->cpt, 'polylang_taxonomies'], 10, 2);
    }

    public static function activate(): void
    {
        Football_Data_Settings::ensure_defaults();

        $cpt = new Football_Data_CPT();
        $cpt->register();
        flush_rewrite_rules();
    }

    public static function deactivate(): void
    {
        flush_rewrite_rules();
    }
}
