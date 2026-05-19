<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

function football_team_meta(string $key): mixed
{
    return get_post_meta(get_the_ID(), $key, true);
}

function football_team_image_url(mixed $value): string
{
    if (is_numeric($value)) {
        return wp_get_attachment_image_url((int) $value, 'full') ?: '';
    }

    return esc_url_raw((string) $value);
}

function football_team_league(mixed $league_api_id): ?WP_Post
{
    $league_api_id = absint($league_api_id);
    if (!$league_api_id) {
        return null;
    }

    $leagues = get_posts([
        'post_type' => 'football_league',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'meta_key' => 'football_api_id',
        'meta_value' => (string) $league_api_id,
    ]);

    return $leagues[0] ?? null;
}

function football_team_post_by_id(mixed $post_id): ?WP_Post
{
    $post_id = absint($post_id);
    if (!$post_id) {
        return null;
    }

    $post = get_post($post_id);

    return $post instanceof WP_Post ? $post : null;
}

function football_team_format_match_date(string $value): string
{
    if ($value === '') {
        return '';
    }

    $timestamp = strtotime($value);
    if (!$timestamp) {
        return $value;
    }

    return wp_date('d.m.Y H:i', $timestamp);
}

function football_team_match_link(mixed $post_id, string $fallback): string
{
    $post = football_team_post_by_id($post_id);
    if (!$post) {
        return esc_html($fallback);
    }

    return '<a href="' . esc_url(get_permalink($post)) . '">' . esc_html(get_the_title($post)) . '</a>';
}

function football_team_standing_row(?WP_Post $league, mixed $team_api_id): array
{
    if (!$league || !$team_api_id) {
        return [];
    }

    $standings = get_post_meta($league->ID, 'football_standings', true);
    if (!is_array($standings)) {
        return [];
    }

    foreach ($standings as $group) {
        if (!is_array($group)) {
            continue;
        }

        foreach ($group as $row) {
            if ((string) ($row['team']['id'] ?? '') === (string) $team_api_id) {
                return $row;
            }
        }
    }

    return [];
}
?>

<main id="main" class="team-page">
    <?php while (have_posts()) : ?>
        <?php the_post(); ?>
        <?php
        $api_id = football_team_meta('football_api_id');
        $logo = football_team_image_url(football_team_meta('football_logo'));
        $code = football_team_meta('football_team_code');
        $country = football_team_meta('football_country');
        $national = football_team_meta('football_national_team');
        $city = football_team_meta('football_city');
        $stadium = football_team_meta('football_stadium');
        $founded = football_team_meta('football_founded');
        $league_api_id = football_team_meta('football_league_api_id');
        $league = football_team_league($league_api_id);
        $league_season = football_team_post_by_id(football_team_meta('football_lg_season_post_id'));
        $venue = football_team_post_by_id(football_team_meta('football_venue_post_id'));
        $venue_address = $venue ? get_post_meta($venue->ID, 'football_venue_address', true) : football_team_meta('football_venue_address');
        $venue_capacity = $venue ? get_post_meta($venue->ID, 'football_venue_capacity', true) : football_team_meta('football_venue_capacity');
        $venue_surface = $venue ? get_post_meta($venue->ID, 'football_venue_surface', true) : football_team_meta('football_venue_surface');
        $venue_image = football_team_image_url($venue ? get_post_meta($venue->ID, 'football_venue_image', true) : football_team_meta('football_venue_image'));
        $city = $venue ? get_post_meta($venue->ID, 'football_city', true) : $city;
        $stadium = $venue ? get_the_title($venue) : $stadium;
        $standing = football_team_standing_row($league, $api_id);

        $fixtures = get_posts([
            'post_type' => 'football_fixture',
            'post_status' => 'publish',
            'posts_per_page' => 14,
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_key' => 'football_match_datetime',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'football_league_api_id',
                    'value' => (string) $league_api_id,
                ],
                [
                    'relation' => 'OR',
                    [
                        'key' => 'football_home_team_post_id',
                        'value' => (string) get_the_ID(),
                    ],
                    [
                        'key' => 'football_away_team_post_id',
                        'value' => (string) get_the_ID(),
                    ],
                    [
                        'key' => 'football_home_team',
                        'value' => get_the_title(),
                    ],
                    [
                        'key' => 'football_away_team',
                        'value' => get_the_title(),
                    ],
                ],
            ],
        ]);
        ?>

        <section class="team-hero">
            <div class="container team-hero__inner">
                <p class="breadcrumbs">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php football_esc_html_t('home'); ?></a>
                    <span>/</span>
                    <span><?php echo esc_html__('Команда', 'football'); ?></span>
                </p>
                <div class="team-hero__title">
                    <?php if ($logo) : ?>
                        <img src="<?php echo esc_url($logo); ?>" alt="">
                    <?php endif; ?>
                    <div>
                        <p class="eyebrow"><?php echo esc_html($code ?: 'Team'); ?> · API ID <?php echo esc_html($api_id); ?></p>
                        <h1><?php the_title(); ?></h1>
                        <p>
                            <?php echo esc_html(implode(' · ', array_filter([$city, $country, $stadium]))); ?>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="container team-layout">
            <div class="team-main">
                <article class="team-panel">
                    <h2><?php echo esc_html__('Информация команды', 'football'); ?></h2>
                    <dl class="team-facts">
                        <div><dt>Страна</dt><dd><?php echo esc_html($country ?: '-'); ?></dd></div>
                        <div><dt>Код</dt><dd><?php echo esc_html($code ?: '-'); ?></dd></div>
                        <div><dt>Сборная</dt><dd><?php echo esc_html($national ? 'Да' : 'Нет'); ?></dd></div>
                        <div><dt>Город</dt><dd><?php echo esc_html($city ?: '-'); ?></dd></div>
                        <div>
                            <dt>Стадион</dt>
                            <dd>
                                <?php if ($venue) : ?>
                                    <a href="<?php echo esc_url(get_permalink($venue)); ?>"><?php echo esc_html(get_the_title($venue)); ?></a>
                                <?php else : ?>
                                    <?php echo esc_html($stadium ?: '-'); ?>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div><dt>Год основания</dt><dd><?php echo esc_html($founded ?: '-'); ?></dd></div>
                        <div>
                            <dt>Турнир</dt>
                            <dd>
                                <?php if ($league) : ?>
                                    <a href="<?php echo esc_url(get_permalink($league)); ?>"><?php echo esc_html(get_the_title($league)); ?></a>
                                <?php else : ?>
                                    <?php echo esc_html($league_api_id ?: '-'); ?>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt>Сезон</dt>
                            <dd><?php echo $league_season ? esc_html(get_the_title($league_season)) : '-'; ?></dd>
                        </div>
                    </dl>

                    <?php if (trim(get_the_content()) !== '') : ?>
                        <div class="entry-content team-content">
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>
                </article>

                <article class="team-panel">
                    <h2><?php echo esc_html__('Стадион', 'football'); ?></h2>
                    <div class="team-venue">
                        <?php if ($venue_image) : ?>
                            <img src="<?php echo esc_url($venue_image); ?>" alt="">
                        <?php endif; ?>
                        <dl class="team-facts">
                            <div><dt>Название</dt><dd><?php echo esc_html($stadium ?: '-'); ?></dd></div>
                            <div><dt>Адрес</dt><dd><?php echo esc_html($venue_address ?: '-'); ?></dd></div>
                            <div><dt>Город</dt><dd><?php echo esc_html($city ?: '-'); ?></dd></div>
                            <div><dt>Вместимость</dt><dd><?php echo esc_html($venue_capacity ?: '-'); ?></dd></div>
                            <div><dt>Покрытие</dt><dd><?php echo esc_html($venue_surface ?: '-'); ?></dd></div>
                        </dl>
                    </div>
                </article>

                <article class="team-panel">
                    <h2><?php echo esc_html__('Матчи команды', 'football'); ?></h2>
                    <?php if ($fixtures) : ?>
                        <div class="team-fixtures">
                            <?php foreach ($fixtures as $fixture) : ?>
                                <?php
                                $home = get_post_meta($fixture->ID, 'football_home_team', true);
                                $away = get_post_meta($fixture->ID, 'football_away_team', true);
                                $home_score = get_post_meta($fixture->ID, 'football_home_score', true);
                                $away_score = get_post_meta($fixture->ID, 'football_away_score', true);
                                $date = get_post_meta($fixture->ID, 'football_match_datetime', true);
                                $home_id = get_post_meta($fixture->ID, 'football_home_team_post_id', true);
                                $away_id = get_post_meta($fixture->ID, 'football_away_team_post_id', true);
                                ?>
                                <div class="team-fixture">
                                    <span><?php echo esc_html(football_team_format_match_date($date)); ?></span>
                                    <strong><?php echo wp_kses_post(football_team_match_link($home_id, $home)); ?></strong>
                                    <a class="team-fixture-match" href="<?php echo esc_url(get_permalink($fixture)); ?>">
                                        <?php echo esc_html($home_score !== '' || $away_score !== '' ? $home_score . ':' . $away_score : 'vs'); ?>
                                    </a>
                                    <strong><?php echo wp_kses_post(football_team_match_link($away_id, $away)); ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="team-muted">Матчи пока не загружены.</p>
                    <?php endif; ?>
                </article>
            </div>

            <aside class="team-side">
                <section class="team-panel">
                    <h2><?php echo esc_html__('Положение в таблице', 'football'); ?></h2>
                    <?php if ($standing) : ?>
                        <dl class="team-standing">
                            <div><dt>Место</dt><dd><?php echo esc_html($standing['rank'] ?? '-'); ?></dd></div>
                            <div><dt>Очки</dt><dd><?php echo esc_html($standing['points'] ?? '-'); ?></dd></div>
                            <div><dt>Матчи</dt><dd><?php echo esc_html($standing['all']['played'] ?? '-'); ?></dd></div>
                            <div><dt>Победы</dt><dd><?php echo esc_html($standing['all']['win'] ?? '-'); ?></dd></div>
                            <div><dt>Ничьи</dt><dd><?php echo esc_html($standing['all']['draw'] ?? '-'); ?></dd></div>
                            <div><dt>Поражения</dt><dd><?php echo esc_html($standing['all']['lose'] ?? '-'); ?></dd></div>
                            <div><dt>Голы</dt><dd><?php echo esc_html(($standing['all']['goals']['for'] ?? '-') . ':' . ($standing['all']['goals']['against'] ?? '-')); ?></dd></div>
                            <div><dt>Разница</dt><dd><?php echo esc_html($standing['goalsDiff'] ?? '-'); ?></dd></div>
                        </dl>
                    <?php else : ?>
                        <p class="team-muted">Положение появится после загрузки таблицы турнира.</p>
                    <?php endif; ?>
                </section>

                <?php if ($league) : ?>
                    <section class="team-panel">
                        <h2><?php echo esc_html__('Турнир', 'football'); ?></h2>
                        <p class="team-linked-league">
                            <a href="<?php echo esc_url(get_permalink($league)); ?>"><?php echo esc_html(get_the_title($league)); ?></a>
                        </p>
                    </section>
                <?php endif; ?>
            </aside>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
