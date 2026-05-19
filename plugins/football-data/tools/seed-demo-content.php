<?php

if (!defined('ABSPATH')) {
    fwrite(STDERR, "Run this file through WP-CLI: wp eval-file tools/seed-demo-content.php\n");
    exit(1);
}

function football_seed_post(string $post_type, string $title, string $slug, string $content = '', array $meta = []): int
{
    $existing = get_page_by_path($slug, OBJECT, $post_type);

    $post_data = [
        'post_type' => $post_type,
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => $content,
        'post_status' => 'publish',
    ];

    if ($existing) {
        $post_data['ID'] = $existing->ID;
        $post_id = wp_update_post($post_data, true);
    } else {
        $post_id = wp_insert_post($post_data, true);
    }

    if (is_wp_error($post_id)) {
        throw new RuntimeException($post_id->get_error_message());
    }

    if (function_exists('pll_set_post_language')) {
        pll_set_post_language($post_id, 'ru');
    }

    foreach ($meta as $key => $value) {
        if (function_exists('carbon_set_post_meta')) {
            carbon_set_post_meta($post_id, $key, $value);
        } else {
            update_post_meta($post_id, $key, $value);
        }
    }

    return (int) $post_id;
}

function football_seed_term(string $taxonomy, string $name, string $slug): int
{
    $existing = term_exists($slug, $taxonomy);

    if (!$existing) {
        $existing = wp_insert_term($name, $taxonomy, ['slug' => $slug]);
    }

    if (is_wp_error($existing)) {
        throw new RuntimeException($existing->get_error_message());
    }

    $term_id = is_array($existing) ? (int) $existing['term_id'] : (int) $existing;

    if (function_exists('pll_set_term_language')) {
        pll_set_term_language($term_id, 'ru');
    }

    return $term_id;
}

$england = football_seed_term('football_country', 'Англия', 'england');
$spain = football_seed_term('football_country', 'Испания', 'spain');
$season = football_seed_term('football_season', '2023/24', '2023-24');
$forward = football_seed_term('football_position', 'Нападающий', 'forward');

$league_id = football_seed_post(
    'football_league',
    'АПЛ',
    'premier-league',
    'Английская Премьер-лига: таблицы, матчи, команды, игроки и новости сезона.',
    [
        'football_api_id' => '39',
        'football_country' => 'Англия',
        'football_season' => '2023/24',
        'football_league_type' => 'league',
        'football_short_description' => 'Главный футбольный чемпионат Англии.',
    ]
);
wp_set_object_terms($league_id, [$england], 'football_country');
wp_set_object_terms($league_id, [$season], 'football_season');

$team_id = football_seed_post(
    'football_team',
    'Манчестер Сити',
    'manchester-city',
    'Профиль команды, состав, матчи, статистика и новости Манчестер Сити.',
    [
        'football_api_id' => '50',
        'football_country' => 'Англия',
        'football_city' => 'Манчестер',
        'football_stadium' => 'Этихад',
        'football_founded' => '1880',
        'football_short_description' => 'Один из сильнейших клубов Англии и Европы.',
    ]
);
wp_set_object_terms($team_id, [$england], 'football_country');

$real_id = football_seed_post(
    'football_team',
    'Реал Мадрид',
    'real-madrid',
    'Профиль команды, состав, матчи, статистика и новости Реал Мадрид.',
    [
        'football_api_id' => '541',
        'football_country' => 'Испания',
        'football_city' => 'Мадрид',
        'football_stadium' => 'Сантьяго Бернабеу',
        'football_founded' => '1902',
        'football_short_description' => 'Один из самых титулованных футбольных клубов мира.',
    ]
);
wp_set_object_terms($real_id, [$spain], 'football_country');

$player_id = football_seed_post(
    'football_player',
    'Эрлинг Холанд',
    'erling-haaland',
    'Эрлинг Холанд — норвежский нападающий английского клуба «Манчестер Сити» и сборной Норвегии.',
    [
        'football_api_id' => '1100',
        'football_first_name' => 'Эрлинг',
        'football_last_name' => 'Холанд',
        'football_birth_date' => '2000-07-21',
        'football_birth_place' => 'Лидс',
        'football_birth_country' => 'Англия',
        'football_nationality' => 'Норвегия',
        'football_height' => '194 см',
        'football_weight' => '87 кг',
        'football_number' => '9',
        'football_position' => 'Нападающий',
        'football_current_team' => 'Манчестер Сити',
        'football_average_rating' => '8.1',
        'football_about' => 'Один из самых результативных форвардов мира, известный мощной игрой в штрафной и стабильной реализацией моментов.',
        'football_season_stats' => [
            [
                'season' => '2023/24',
                'league' => 'АПЛ',
                'team' => 'Манчестер Сити',
                'matches' => '31',
                'goals' => '27',
                'assists' => '5',
                'yellow_cards' => '2',
                'red_cards' => '0',
                'minutes' => '2410',
            ],
            [
                'season' => '2023/24',
                'league' => 'Лига чемпионов',
                'team' => 'Манчестер Сити',
                'matches' => '11',
                'goals' => '12',
                'assists' => '1',
                'yellow_cards' => '1',
                'red_cards' => '0',
                'minutes' => '906',
            ],
        ],
        'football_career' => [
            ['period' => '2022 - н.в.', 'team' => 'Манчестер Сити', 'matches' => '85', 'goals' => '80'],
            ['period' => '2020 - 2022', 'team' => 'Боруссия Дортмунд', 'matches' => '89', 'goals' => '86'],
            ['period' => '2019 - 2020', 'team' => 'РБ Зальцбург', 'matches' => '27', 'goals' => '29'],
        ],
        'football_achievements' => [
            ['title' => 'АПЛ', 'count' => '2'],
            ['title' => 'Лига чемпионов УЕФА', 'count' => '1'],
            ['title' => 'Кубок Англии', 'count' => '1'],
        ],
    ]
);
wp_set_object_terms($player_id, [$england], 'football_country');
wp_set_object_terms($player_id, [$season], 'football_season');
wp_set_object_terms($player_id, [$forward], 'football_position');

$fixture_id = football_seed_post(
    'football_fixture',
    'Манчестер Сити — Реал Мадрид',
    'manchester-city-real-madrid-2024-05-17',
    'Матч-центр: счет, события, составы, статистика и коэффициенты.',
    [
        'football_api_id' => '9001',
        'football_league_name' => 'Лига чемпионов',
        'football_round' => '1/2 финала',
        'football_match_datetime' => '2024-05-17 22:00:00',
        'football_venue' => 'Этихад',
        'football_status' => 'Завершен',
        'football_home_team' => 'Манчестер Сити',
        'football_away_team' => 'Реал Мадрид',
        'football_home_score' => '2',
        'football_away_score' => '1',
        'football_match_statistics' => [
            ['label' => 'Владение мячом', 'home' => '61%', 'away' => '39%'],
            ['label' => 'Удары', 'home' => '18', 'away' => '7'],
            ['label' => 'Удары в створ', 'home' => '7', 'away' => '3'],
        ],
        'football_match_odds' => [
            ['market' => 'Основной исход', 'home' => '1.62', 'draw' => '4.20', 'away' => '6.00'],
        ],
    ]
);
wp_set_object_terms($fixture_id, [$season], 'football_season');

$bookmakers = [
    ['Winline', 'winline', '4.9', '1000 ₽', '100 ₽'],
    ['Leon', 'leon', '4.7', '2000 ₽', '100 ₽'],
    ['Pari', 'pari', '4.6', '500 ₽', '100 ₽'],
];

foreach ($bookmakers as [$name, $slug, $rating, $bonus, $deposit]) {
    $bookmaker_id = football_seed_post(
        'football_bookmaker',
        $name,
        $slug,
        sprintf('Обзор букмекера %s: бонусы, рейтинг, условия и особенности.', $name),
        [
            'football_rating' => $rating,
            'football_bonus' => $bonus,
            'football_min_deposit' => $deposit,
            'football_countries' => 'Россия, Европа',
            'football_affiliate_url' => '#',
            'football_review_summary' => 'Демо-описание букмекера для разработки шаблона.',
            'football_features' => [
                ['title' => 'Live', 'description' => 'Ставки по ходу матча.'],
                ['title' => 'Мобильное приложение', 'description' => 'Удобная игра со смартфона.'],
            ],
        ]
    );
    wp_set_object_terms($bookmaker_id, [$england], 'football_country');
}

$news_id = football_seed_post(
    'post',
    'Холанд побил рекорд по голам за сезон АПЛ',
    'haaland-goal-record-premier-league',
    'Эрлинг Холанд продолжает впечатлять результативностью и обновляет рекордные показатели сезона.',
    [
        'football_api_source_id' => 'demo-news-101',
    ]
);

echo "Demo content seeded:\n";
echo "League: {$league_id}\n";
echo "Team: {$team_id}\n";
echo "Player: {$player_id}\n";
echo "Fixture: {$fixture_id}\n";
echo "News: {$news_id}\n";
