<?php

$wp_load = dirname(__DIR__, 4) . '/wp-load.php';

if (!defined('ABSPATH')) {
    if (!file_exists($wp_load)) {
        fwrite(STDERR, "wp-load.php not found at {$wp_load}\n");
        exit(1);
    }

    require_once $wp_load;
}

function football_seed_simple_post(string $post_type, string $title, string $slug, string $content, array $meta = []): int
{
    $existing = get_page_by_path($slug, OBJECT, $post_type);
    $post_data = [
        'post_type' => $post_type,
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => $content,
        'post_status' => 'publish',
    ];

    if ($existing instanceof WP_Post) {
        $post_data['ID'] = $existing->ID;
        $post_id = wp_update_post($post_data, true);
    } else {
        $post_id = wp_insert_post($post_data, true);
    }

    if (is_wp_error($post_id)) {
        throw new RuntimeException($post_id->get_error_message());
    }

    if (function_exists('pll_set_post_language')) {
        pll_set_post_language((int) $post_id, 'ru');
    }

    foreach ($meta as $key => $value) {
        update_post_meta((int) $post_id, $key, $value);
    }

    return (int) $post_id;
}

$bookmakers = [
    [
        'Winline',
        'winline',
        '<p>Winline — демо-страница букмекера для проверки карточек, обзора и партнерской ссылки. Здесь можно хранить редакционный обзор, условия бонуса и заметки для SEO.</p>',
        [
            'football_rating' => '4.9',
            'football_bonus' => '1000 ₽',
            'football_min_deposit' => '100 ₽',
            'football_countries' => 'Россия, Европа',
            'football_affiliate_url' => 'https://example.com/winline',
            'football_review_summary' => 'Высокий рейтинг, понятные условия бонуса и удобная линия для футбольных матчей.',
            'football_features' => [
                ['title' => 'Live-ставки', 'description' => 'Линия по ходу матча и быстрый доступ к основным рынкам.'],
                ['title' => 'Бонус новичкам', 'description' => 'Отдельное поле для промо, которое редактор меняет вручную.'],
                ['title' => 'Футбольная линия', 'description' => 'Карточка подходит для обзоров матчей, турниров и коэффициентов.'],
            ],
        ],
    ],
    [
        'Leon',
        'leon',
        '<p>Leon — демо-обзор букмекера с ключевыми условиями, кратким описанием и списком особенностей. Эти данные не зависят от API и редактируются в WordPress.</p>',
        [
            'football_rating' => '4.7',
            'football_bonus' => '2000 ₽',
            'football_min_deposit' => '100 ₽',
            'football_countries' => 'Россия, СНГ',
            'football_affiliate_url' => 'https://example.com/leon',
            'football_review_summary' => 'Подходит для демонстрации рейтинга, бонуса, стран и перехода на партнерскую ссылку.',
            'football_features' => [
                ['title' => 'Быстрый старт', 'description' => 'Минимальный депозит и бонус видны сразу в карточке.'],
                ['title' => 'Обзор условий', 'description' => 'На странице можно раскрывать правила, лимиты и сильные стороны.'],
                ['title' => 'Ручная редактура', 'description' => 'Коммерческие тексты остаются под контролем редактора.'],
            ],
        ],
    ],
];

$news = [
    [
        'АПЛ обновила календарь центральных матчей тура',
        'premier-league-calendar-update',
        '<p>Организаторы английской Премьер-лиги обновили расписание центральных матчей ближайшего тура. В демо-новости можно проверить обычную страницу записи, дату публикации и вывод на главной.</p>',
        ['football_api_source_id' => 'demo-news-001'],
    ],
    [
        'Тренеры назвали составы на решающий матч недели',
        'coaches-lineups-week-match',
        '<p>Перед главным матчем недели тренерские штабы объявили предварительные составы. Эта запись нужна как второй пример новости для главной страницы и архива.</p>',
        ['football_api_source_id' => 'demo-news-002'],
    ],
];

$created = [];

foreach ($bookmakers as [$title, $slug, $content, $meta]) {
    $created[] = 'bookmaker:' . football_seed_simple_post('football_bookmaker', $title, $slug, $content, $meta);
}

foreach ($news as [$title, $slug, $content, $meta]) {
    $created[] = 'news:' . football_seed_simple_post('post', $title, $slug, $content, $meta);
}

flush_rewrite_rules(false);

echo implode("\n", $created) . "\n";
