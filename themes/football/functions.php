<?php

if (!defined('ABSPATH')) {
    exit;
}

function football_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', [
        'height' => 80,
        'width' => 240,
        'flex-height' => true,
        'flex-width' => true,
    ]);

    register_nav_menus([
        'primary' => __('Primary Menu', 'football'),
    ]);
}
add_action('after_setup_theme', 'football_setup');

function football_assets(): void
{
    $theme_version = wp_get_theme()->get('Version');

    wp_enqueue_style(
        'football-main',
        get_template_directory_uri() . '/assets/css/main.css',
        [],
        $theme_version
    );

    wp_enqueue_script(
        'football-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        $theme_version,
        true
    );
}
add_action('wp_enqueue_scripts', 'football_assets');

function football_excerpt_more(): string
{
    return '...';
}
add_filter('excerpt_more', 'football_excerpt_more');

function football_string_translations(): array
{
    return [
        'site.section.football' => 'Футбол',
        'site.home.subtitle' => 'Новости, матчи, игроки, турниры и букмекеры в одном месте.',
        'site.quick_links' => 'Быстрые ссылки',
        'site.archive' => 'Архив',
        'site.no_posts' => 'Пока нет записей.',
        'home.summary' => 'Сводка',
        'home.featured_leagues' => 'Турниры в базе',
        'home.featured_teams' => 'Команды',
        'home.more_sections' => 'Ещё на сайте',
        'home.leagues_count' => 'Турниров',
        'home.teams_count' => 'Команд',
        'home.matches_count' => 'Матчей',
        'home.venues_count' => 'Стадионов',
        'home.season' => 'Сезон',
        'home.country' => 'Страна',
        'home.teams' => 'Команды',
        'home.matches' => 'Матчи',
        'home.league' => 'Турнир',
        'home.stadium' => 'Стадион',
        'home.founded' => 'Основан',
        'home.not_set' => 'Не указано',
        'site.back_home' => 'На главную',
        'site.skip_to_content' => 'Перейти к содержимому',
        'site.primary_navigation' => 'Основная навигация',
        'site.language_switcher' => 'Переключатель языка',
        'section.leagues' => 'Турниры',
        'section.teams' => 'Команды',
        'section.players' => 'Игроки',
        'section.fixtures' => 'Матчи',
        'section.bookmakers' => 'Букмекеры',
        'section.news' => 'Новости',
        'archive.leagues' => 'Все турниры',
        'archive.teams' => 'Все команды',
        'archive.players' => 'Все игроки',
        'archive.fixtures' => 'Все матчи',
        'archive.bookmakers' => 'Все букмекеры',
        'archive.news' => 'Все новости',
        'player.birth_date' => 'Дата рождения',
        'player.birth_place' => 'Место рождения',
        'player.nationality' => 'Гражданство',
        'player.height' => 'Рост',
        'player.weight' => 'Вес',
        'player.average_rating' => 'Средняя оценка',
        'player.about' => 'О себе',
        'player.matches' => 'Матчи',
        'player.goals' => 'Голы',
        'player.assists' => 'Передачи',
        'player.characteristics' => 'Характеристики',
        'player.positions' => 'Позиции на поле',
        'player.main_position' => 'Основная позиция:',
        'player.additional_positions' => 'Дополнительные позиции: второй нападающий, левый нападающий.',
        'player.season_stats' => 'Статистика за сезон',
        'player.league' => 'Турнир',
        'player.assists_short' => 'ГП',
        'player.yellow_cards_short' => 'ЖК',
        'player.red_cards_short' => 'КК',
        'player.minutes' => 'Минуты',
        'player.career' => 'Карьера',
        'player.period' => 'Период',
        'player.team' => 'Команда',
    ];
}

function football_register_polylang_strings(): void
{
    if (!function_exists('pll_register_string')) {
        return;
    }

    foreach (football_string_translations() as $key => $value) {
        pll_register_string('football_' . $key, $value, 'Football theme');
    }
}
add_action('init', 'football_register_polylang_strings');

function football_t(string $key): string
{
    $strings = football_string_translations();
    $fallback = $strings[$key] ?? $key;

    if (function_exists('pll__')) {
        return pll__($fallback);
    }

    return $fallback;
}

function football_esc_html_t(string $key): void
{
    echo esc_html(football_t($key));
}

function football_esc_attr_t(string $key): void
{
    echo esc_attr(football_t($key));
}
