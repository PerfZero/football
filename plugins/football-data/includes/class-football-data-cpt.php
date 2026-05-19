<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Football_Data_CPT
{
    private const POST_TYPES = [
        'football_league',
        'football_team',
        'football_player',
        'football_fixture',
        'football_bookmaker',
    ];

    private const TAXONOMIES = [
        'football_country',
        'football_season',
        'football_position',
        'football_news_topic',
        'football_bookmaker_feature',
    ];

    public function register(): void
    {
        $this->register_post_types();
        $this->register_taxonomies();
    }

    public function polylang_post_types(array $post_types, bool $is_settings): array
    {
        return array_values(array_unique(array_merge($post_types, self::POST_TYPES)));
    }

    public function polylang_taxonomies(array $taxonomies, bool $is_settings): array
    {
        return array_values(array_unique(array_merge($taxonomies, self::TAXONOMIES)));
    }

    private function register_post_types(): void
    {
        $this->post_type('football_league', 'Турниры', 'Турнир', 'leagues', 'dashicons-awards');
        $this->post_type('football_team', 'Команды', 'Команда', 'teams', 'dashicons-groups');
        $this->post_type('football_player', 'Игроки', 'Игрок', 'players', 'dashicons-id');
        $this->post_type('football_fixture', 'Матчи', 'Матч', 'matches', 'dashicons-calendar-alt');
        $this->post_type('football_bookmaker', 'Букмекеры', 'Букмекер', 'bookmakers', 'dashicons-star-filled');
    }

    private function post_type(string $key, string $plural, string $single, string $slug, string $icon): void
    {
        $single_lower = function_exists('mb_strtolower') ? mb_strtolower($single) : strtolower($single);

        register_post_type($key, [
            'labels' => [
                'name' => $plural,
                'singular_name' => $single,
                'add_new' => 'Добавить',
                'add_new_item' => 'Добавить ' . $single_lower,
                'edit_item' => 'Редактировать ' . $single_lower,
                'new_item' => 'Новый ' . $single_lower,
                'view_item' => 'Открыть ' . $single_lower,
                'search_items' => 'Искать',
                'not_found' => 'Ничего не найдено',
            ],
            'public' => true,
            'show_in_rest' => true,
            'menu_icon' => $icon,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
            'has_archive' => true,
            'rewrite' => ['slug' => $slug],
        ]);
    }

    private function register_taxonomies(): void
    {
        $this->taxonomy('football_country', 'Страны', 'Страна', ['football_league', 'football_team', 'football_player', 'football_bookmaker'], 'countries');
        $this->taxonomy('football_season', 'Сезоны', 'Сезон', ['football_league', 'football_fixture', 'football_player'], 'seasons');
        $this->taxonomy('football_position', 'Позиции', 'Позиция', ['football_player'], 'positions');
        $this->taxonomy('football_news_topic', 'Темы новостей', 'Тема новости', ['post'], 'football-topics');
        $this->taxonomy('football_bookmaker_feature', 'Особенности букмекеров', 'Особенность', ['football_bookmaker'], 'bookmaker-features');
    }

    private function taxonomy(string $key, string $plural, string $single, array $post_types, string $slug): void
    {
        register_taxonomy($key, $post_types, [
            'labels' => [
                'name' => $plural,
                'singular_name' => $single,
                'search_items' => 'Искать',
                'all_items' => 'Все',
                'edit_item' => 'Редактировать',
                'update_item' => 'Обновить',
                'add_new_item' => 'Добавить',
                'new_item_name' => 'Новое название',
            ],
            'public' => true,
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => $slug],
        ]);
    }
}
