<?php

if (!defined('ABSPATH')) {
    exit;
}

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

final class Football_Data_Carbon_Fields
{
    public function boot(): void
    {
        if (!class_exists(Carbon_Fields::class)) {
            return;
        }

        Carbon_Fields::boot();
    }

    public function admin_notice(): void
    {
        if (class_exists(Carbon_Fields::class)) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        $message = football_data()->i18n->translate('carbon_missing');

        echo '<div class="notice notice-error"><p><strong>Football Data:</strong> ' . esc_html($message) . '</p></div>';
    }

    public function register_fields(): void
    {
        if (!class_exists(Container::class) || !class_exists(Field::class)) {
            return;
        }

        $this->register_league_fields();
        $this->register_team_fields();
        $this->register_player_fields();
        $this->register_fixture_fields();
        $this->register_bookmaker_fields();
        $this->register_news_fields();
    }

    private function register_league_fields(): void
    {
        Container::make('post_meta', 'Данные турнира')
            ->where('post_type', '=', 'football_league')
            ->add_fields([
                Field::make('text', 'football_api_id', 'API ID'),
                Field::make('image', 'football_logo', 'Логотип'),
                Field::make('text', 'football_country', 'Страна'),
                Field::make('text', 'football_country_code', 'Код страны'),
                Field::make('image', 'football_country_flag', 'Флаг страны'),
                Field::make('text', 'football_season', 'Сезон'),
                Field::make('text', 'football_season_start', 'Дата начала сезона'),
                Field::make('text', 'football_season_end', 'Дата окончания сезона'),
                Field::make('checkbox', 'football_season_current', 'Текущий сезон'),
                Field::make('select', 'football_league_type', 'Тип турнира')
                    ->set_options([
                        'league' => 'Лига',
                        'cup' => 'Кубок',
                    ]),
                Field::make('textarea', 'football_short_description', 'Короткое описание'),
            ]);
    }

    private function register_team_fields(): void
    {
        Container::make('post_meta', 'Данные команды')
            ->where('post_type', '=', 'football_team')
            ->add_fields([
                Field::make('text', 'football_api_id', 'API ID'),
                Field::make('image', 'football_logo', 'Логотип'),
                Field::make('text', 'football_country', 'Страна'),
                Field::make('text', 'football_city', 'Город'),
                Field::make('text', 'football_stadium', 'Стадион'),
                Field::make('text', 'football_founded', 'Год основания'),
                Field::make('text', 'football_league_api_id', 'API ID основной лиги'),
                Field::make('textarea', 'football_short_description', 'Короткое описание'),
            ]);
    }

    private function register_player_fields(): void
    {
        Container::make('post_meta', 'Профиль игрока')
            ->where('post_type', '=', 'football_player')
            ->add_fields([
                Field::make('text', 'football_api_id', 'API ID'),
                Field::make('image', 'football_photo', 'Фото игрока'),
                Field::make('text', 'football_first_name', 'Имя'),
                Field::make('text', 'football_last_name', 'Фамилия'),
                Field::make('date', 'football_birth_date', 'Дата рождения'),
                Field::make('text', 'football_birth_place', 'Место рождения'),
                Field::make('text', 'football_birth_country', 'Страна рождения'),
                Field::make('text', 'football_nationality', 'Гражданство'),
                Field::make('text', 'football_height', 'Рост'),
                Field::make('text', 'football_weight', 'Вес'),
                Field::make('text', 'football_number', 'Номер'),
                Field::make('text', 'football_position', 'Позиция'),
                Field::make('text', 'football_current_team', 'Текущая команда'),
                Field::make('text', 'football_league_api_id', 'API ID текущей лиги'),
                Field::make('text', 'football_average_rating', 'Средняя оценка за матч'),
                Field::make('textarea', 'football_about', 'О себе'),
            ]);

        Container::make('post_meta', 'Статистика и карьера')
            ->where('post_type', '=', 'football_player')
            ->add_fields([
                Field::make('complex', 'football_season_stats', 'Статистика по сезонам')
                    ->set_layout('tabbed-horizontal')
                    ->add_fields([
                        Field::make('text', 'season', 'Сезон'),
                        Field::make('text', 'league', 'Турнир'),
                        Field::make('text', 'team', 'Команда'),
                        Field::make('text', 'matches', 'Матчи'),
                        Field::make('text', 'goals', 'Голы'),
                        Field::make('text', 'assists', 'Голевые передачи'),
                        Field::make('text', 'yellow_cards', 'Желтые карточки'),
                        Field::make('text', 'red_cards', 'Красные карточки'),
                        Field::make('text', 'minutes', 'Минуты'),
                    ]),
                Field::make('complex', 'football_career', 'Карьера')
                    ->set_layout('tabbed-horizontal')
                    ->add_fields([
                        Field::make('text', 'period', 'Период'),
                        Field::make('text', 'team', 'Команда'),
                        Field::make('text', 'matches', 'Матчи'),
                        Field::make('text', 'goals', 'Голы'),
                    ]),
                Field::make('complex', 'football_achievements', 'Достижения')
                    ->add_fields([
                        Field::make('text', 'title', 'Название'),
                        Field::make('text', 'count', 'Количество'),
                    ]),
            ]);
    }

    private function register_fixture_fields(): void
    {
        Container::make('post_meta', 'Данные матча')
            ->where('post_type', '=', 'football_fixture')
            ->add_fields([
                Field::make('text', 'football_api_id', 'API ID'),
                Field::make('text', 'football_league_api_id', 'API ID лиги'),
                Field::make('text', 'football_league_name', 'Турнир'),
                Field::make('text', 'football_round', 'Тур/стадия'),
                Field::make('date_time', 'football_match_datetime', 'Дата и время матча'),
                Field::make('text', 'football_venue', 'Стадион'),
                Field::make('text', 'football_status', 'Статус'),
                Field::make('text', 'football_home_team', 'Домашняя команда'),
                Field::make('text', 'football_away_team', 'Гостевая команда'),
                Field::make('text', 'football_home_score', 'Счет хозяев'),
                Field::make('text', 'football_away_score', 'Счет гостей'),
            ]);

        Container::make('post_meta', 'События, статистика и коэффициенты')
            ->where('post_type', '=', 'football_fixture')
            ->add_fields([
                Field::make('complex', 'football_match_events', 'Лента событий')
                    ->add_fields([
                        Field::make('text', 'minute', 'Минута'),
                        Field::make('text', 'team', 'Команда'),
                        Field::make('text', 'player', 'Игрок'),
                        Field::make('select', 'type', 'Тип события')
                            ->set_options([
                                'goal' => 'Гол',
                                'card' => 'Карточка',
                                'substitution' => 'Замена',
                                'var' => 'VAR',
                                'other' => 'Другое',
                            ]),
                        Field::make('text', 'description', 'Описание'),
                    ]),
                Field::make('complex', 'football_match_statistics', 'Статистика матча')
                    ->add_fields([
                        Field::make('text', 'label', 'Показатель'),
                        Field::make('text', 'home', 'Хозяева'),
                        Field::make('text', 'away', 'Гости'),
                    ]),
                Field::make('complex', 'football_match_odds', 'Коэффициенты')
                    ->add_fields([
                        Field::make('text', 'market', 'Рынок'),
                        Field::make('text', 'home', 'П1'),
                        Field::make('text', 'draw', 'X'),
                        Field::make('text', 'away', 'П2'),
                    ]),
            ]);
    }

    private function register_bookmaker_fields(): void
    {
        Container::make('post_meta', 'Данные букмекера')
            ->where('post_type', '=', 'football_bookmaker')
            ->add_fields([
                Field::make('text', 'football_api_id', 'API ID'),
                Field::make('image', 'football_logo', 'Логотип'),
                Field::make('text', 'football_rating', 'Рейтинг'),
                Field::make('text', 'football_bonus', 'Бонус'),
                Field::make('text', 'football_min_deposit', 'Минимальный депозит'),
                Field::make('text', 'football_countries', 'Страны'),
                Field::make('text', 'football_affiliate_url', 'Партнерская ссылка'),
                Field::make('textarea', 'football_review_summary', 'Краткий обзор'),
                Field::make('complex', 'football_features', 'Особенности')
                    ->add_fields([
                        Field::make('text', 'title', 'Название'),
                        Field::make('textarea', 'description', 'Описание'),
                    ]),
            ]);
    }

    private function register_news_fields(): void
    {
        Container::make('post_meta', 'Футбольные связи новости')
            ->where('post_type', '=', 'post')
            ->add_fields([
                Field::make('association', 'football_related_entities', 'Связанные сущности')
                    ->set_types([
                        ['type' => 'post', 'post_type' => 'football_league'],
                        ['type' => 'post', 'post_type' => 'football_team'],
                        ['type' => 'post', 'post_type' => 'football_player'],
                        ['type' => 'post', 'post_type' => 'football_fixture'],
                    ]),
                Field::make('text', 'football_api_source_id', 'ID источника/API'),
            ]);
    }
}
