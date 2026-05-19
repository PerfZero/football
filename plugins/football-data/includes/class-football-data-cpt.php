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
        return $this->add_image_column($columns, 'football_venue_image', 'Фото');
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

    public function team_columns(array $columns): array
    {
        return $this->add_image_column($columns, 'football_team_image', 'Фото');
    }

    public function render_team_column(string $column, int $post_id): void
    {
        if ($column !== 'football_team_image') {
            return;
        }

        $image = $this->image_url(get_post_meta($post_id, 'football_logo', true));
        if ($image === '') {
            echo '<span class="football-data-admin-thumb football-data-admin-thumb--empty">-</span>';
            return;
        }

        echo '<img class="football-data-admin-thumb football-data-admin-thumb--contain" src="' . esc_url($image) . '" alt="">';
    }

    public function player_columns(array $columns): array
    {
        return $this->add_image_column($columns, 'football_player_image', 'Фото');
    }

    public function render_player_column(string $column, int $post_id): void
    {
        if ($column !== 'football_player_image') {
            return;
        }

        $image = $this->image_url(get_post_meta($post_id, 'football_photo', true));
        if ($image === '') {
            echo '<span class="football-data-admin-thumb football-data-admin-thumb--empty">-</span>';
            return;
        }

        echo '<img class="football-data-admin-thumb football-data-admin-thumb--cover" src="' . esc_url($image) . '" alt="">';
    }

    public function fixture_columns(array $columns): array
    {
        return $this->add_image_column($columns, 'football_fixture_image', 'Фото');
    }

    public function render_fixture_column(string $column, int $post_id): void
    {
        if ($column !== 'football_fixture_image') {
            return;
        }

        $home_logo = $this->fixture_team_logo($post_id, 'football_home_team_post_id');
        $away_logo = $this->fixture_team_logo($post_id, 'football_away_team_post_id');

        if ($home_logo === '' && $away_logo === '') {
            echo '<span class="football-data-admin-thumb football-data-admin-thumb--empty">-</span>';
            return;
        }

        echo '<span class="football-data-admin-match-thumbs">';

        foreach ([$home_logo, $away_logo] as $logo) {
            if ($logo === '') {
                echo '<span class="football-data-admin-match-thumb football-data-admin-match-thumb--empty">-</span>';
                continue;
            }

            echo '<img class="football-data-admin-match-thumb" src="' . esc_url($logo) . '" alt="">';
        }

        echo '</span>';
    }

    public function admin_list_styles(): void
    {
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->post_type, ['football_venue', 'football_team', 'football_player', 'football_fixture'], true)) {
            return;
        }

        echo '<style>
            .column-football_venue_image,
            .column-football_team_image,
            .column-football_player_image,
            .column-football_fixture_image { width: 92px; }
            .football-data-admin-thumb {
                width: 72px;
                height: 48px;
                border-radius: 4px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: #f0f0f1;
                color: #646970;
            }
            .football-data-admin-thumb--cover { object-fit: cover; }
            .football-data-admin-thumb--contain { object-fit: contain; padding: 6px; }
            .football-data-admin-match-thumbs {
                display: inline-flex;
                align-items: center;
                gap: 4px;
            }
            .football-data-admin-match-thumb {
                width: 34px;
                height: 34px;
                border-radius: 4px;
                object-fit: contain;
                background: #f0f0f1;
                padding: 4px;
            }
            .football-data-admin-match-thumb--empty {
                display: inline-flex;
                align-items: center;
                justify-content: center;
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

        return $this->image_url($image);
    }

    private function add_image_column(array $columns, string $column_key, string $label): array
    {
        $new_columns = [];

        foreach ($columns as $key => $column_label) {
            if ($key === 'title') {
                $new_columns[$column_key] = $label;
            }

            $new_columns[$key] = $column_label;
        }

        return $new_columns;
    }

    private function image_url(mixed $image): string
    {
        if (is_numeric($image)) {
            return wp_get_attachment_image_url((int) $image, 'thumbnail') ?: '';
        }

        return esc_url_raw((string) $image);
    }

    private function fixture_team_logo(int $fixture_id, string $team_meta_key): string
    {
        $team_id = absint(get_post_meta($fixture_id, $team_meta_key, true));
        if (!$team_id) {
            return '';
        }

        return $this->image_url(get_post_meta($team_id, 'football_logo', true));
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
