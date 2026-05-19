<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Football_Data_CPT
{
    private const POST_TYPES = [
        'football_league',
        'football_lg_season',
        'football_team',
        'football_venue',
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

    public function venue_columns(array $columns): array
    {
        $new_columns = [];

        foreach ($columns as $key => $label) {
            if ($key === 'title') {
                $new_columns['football_venue_image'] = 'Фото';
            }

            $new_columns[$key] = $label;
        }

        return $new_columns;
    }

    public function render_venue_column(string $column, int $post_id): void
    {
        if ($column !== 'football_venue_image') {
            return;
        }

        $image = $this->venue_image_url($post_id);
        if ($image === '') {
            echo '<span class="football-data-admin-thumb football-data-admin-thumb--empty">-</span>';
            return;
        }

        echo '<img class="football-data-admin-thumb" src="' . esc_url($image) . '" alt="">';
    }

    public function admin_list_styles(): void
    {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'football_venue') {
            return;
        }

        echo '<style>
            .column-football_venue_image { width: 92px; }
            .football-data-admin-thumb {
                width: 72px;
                height: 48px;
                border-radius: 4px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                object-fit: cover;
                background: #f0f0f1;
                color: #646970;
            }
        </style>';
    }

    private function register_post_types(): void
    {
        $this->post_type('football_league', 'Турниры', 'Турнир', 'leagues', 'dashicons-awards');
        $this->post_type('football_lg_season', 'Сезоны турниров', 'Сезон турнира', 'league-seasons', 'dashicons-calendar');
        $this->post_type('football_team', 'Команды', 'Команда', 'teams', 'dashicons-groups');
        $this->post_type('football_venue', 'Стадионы', 'Стадион', 'venues', 'dashicons-location-alt');
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

    private function venue_image_url(int $post_id): string
    {
        $image = get_post_meta($post_id, 'football_venue_image', true);
        if (!$image) {
            $image = $this->venue_fallback_team_image($post_id);
        }

        if (is_numeric($image)) {
            return wp_get_attachment_image_url((int) $image, 'thumbnail') ?: '';
        }

        return esc_url_raw((string) $image);
    }

    private function venue_fallback_team_image(int $venue_id): mixed
    {
        $teams = get_posts([
            'post_type' => 'football_team',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => 'football_venue_post_id',
            'meta_value' => (string) $venue_id,
        ]);

        if (!$teams) {
            return '';
        }

        return get_post_meta((int) $teams[0], 'football_venue_image', true);
    }

    private function register_taxonomies(): void
    {
        $this->taxonomy('football_country', 'Страны', 'Страна', ['football_league', 'football_team', 'football_venue', 'football_player', 'football_bookmaker'], 'countries');
        $this->taxonomy('football_season', 'Сезоны', 'Сезон', ['football_league', 'football_lg_season', 'football_fixture', 'football_player'], 'seasons');
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
