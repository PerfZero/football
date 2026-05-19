<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

function football_fixture_meta(string $key): mixed
{
    return get_post_meta(get_the_ID(), $key, true);
}

function football_fixture_post_by_id(mixed $post_id): ?WP_Post
{
    $post_id = absint($post_id);
    if (!$post_id) {
        return null;
    }

    $post = get_post($post_id);

    return $post instanceof WP_Post ? $post : null;
}

function football_fixture_image_url(mixed $value): string
{
    if (is_numeric($value)) {
        return wp_get_attachment_image_url((int) $value, 'thumbnail') ?: '';
    }

    return esc_url_raw((string) $value);
}

function football_fixture_format_date(string $value): string
{
    if ($value === '') {
        return '';
    }

    try {
        return (new DateTimeImmutable($value))->format('d.m.Y H:i');
    } catch (Exception) {
        return $value;
    }
}

function football_fixture_json_meta(string $key): array
{
    $value = football_fixture_meta($key);
    if (is_array($value)) {
        return $value;
    }

    $decoded = json_decode((string) $value, true);

    return is_array($decoded) ? $decoded : [];
}

function football_fixture_team_link(?WP_Post $team, string $fallback): string
{
    if (!$team) {
        return esc_html($fallback);
    }

    return '<a href="' . esc_url(get_permalink($team)) . '">' . esc_html(get_the_title($team)) . '</a>';
}

function football_fixture_score(string $home_score, string $away_score): string
{
    if ($home_score !== '' || $away_score !== '') {
        return ($home_score !== '' ? $home_score : '-') . ':' . ($away_score !== '' ? $away_score : '-');
    }

    return 'vs';
}

function football_fixture_stat_number(string $value): ?float
{
    $value = trim(str_replace('%', '', $value));
    if ($value === '' || !is_numeric($value)) {
        return null;
    }

    return (float) $value;
}

function football_fixture_payload_value(mixed $value): string
{
    if (is_array($value)) {
        $parts = array_filter(array_map(static fn (mixed $item): string => (string) $item, $value), static fn (string $item): bool => $item !== '');

        return $parts ? implode(' - ', $parts) : '-';
    }

    return (string) ($value ?: '-');
}
?>

<main id="main" class="fixture-page">
    <?php while (have_posts()) : ?>
        <?php the_post(); ?>
        <?php
        $api_id = football_fixture_meta('football_api_id');
        $date = football_fixture_meta('football_match_datetime');
        $round = football_fixture_meta('football_round');
        $status = football_fixture_meta('football_status');
        $status_short = football_fixture_meta('football_status_short');
        $elapsed = football_fixture_meta('football_status_elapsed');
        $extra = football_fixture_meta('football_status_extra');
        $referee = football_fixture_meta('football_referee');
        $timezone = football_fixture_meta('football_timezone');
        $timestamp = football_fixture_meta('football_timestamp');
        $league_name = football_fixture_meta('football_league_name');
        $venue_name = football_fixture_meta('football_venue');
        $venue_city = football_fixture_meta('football_venue_city');
        $home_name = football_fixture_meta('football_home_team');
        $away_name = football_fixture_meta('football_away_team');
        $home_score = (string) football_fixture_meta('football_home_score');
        $away_score = (string) football_fixture_meta('football_away_score');

        $league = football_fixture_post_by_id(football_fixture_meta('football_league_post_id'));
        $league_season = football_fixture_post_by_id(football_fixture_meta('football_lg_season_post_id'));
        $venue = football_fixture_post_by_id(football_fixture_meta('football_venue_post_id'));
        $home_team = football_fixture_post_by_id(football_fixture_meta('football_home_team_post_id'));
        $away_team = football_fixture_post_by_id(football_fixture_meta('football_away_team_post_id'));

        $home_logo = $home_team ? football_fixture_image_url(get_post_meta($home_team->ID, 'football_logo', true)) : '';
        $away_logo = $away_team ? football_fixture_image_url(get_post_meta($away_team->ID, 'football_logo', true)) : '';
        $events = football_fixture_meta('football_match_events');
        $events = is_array($events) ? $events : [];
        $statistics = football_fixture_meta('football_match_statistics');
        $statistics = is_array($statistics) ? $statistics : [];
        $odds = football_fixture_meta('football_match_odds');
        $odds = is_array($odds) ? $odds : [];
        $score_payload = football_fixture_json_meta('football_score_payload');
        $periods_payload = football_fixture_json_meta('football_periods_payload');
        ?>

        <section class="fixture-hero">
            <div class="container fixture-hero__inner">
                <p class="breadcrumbs">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html__('Главная', 'football'); ?></a>
                    <span>/</span>
                    <span><?php echo esc_html__('Матч', 'football'); ?></span>
                </p>

                <div class="fixture-scoreboard">
                    <div class="fixture-scoreboard__meta">
                        <span><?php echo esc_html($league ? get_the_title($league) : ($league_name ?: '-')); ?></span>
                        <?php if ($round) : ?>
                            <span><?php echo esc_html($round); ?></span>
                        <?php endif; ?>
                        <?php if ($date) : ?>
                            <span><?php echo esc_html(football_fixture_format_date($date)); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="fixture-scoreboard__teams">
                        <div class="fixture-team fixture-team--home">
                            <?php if ($home_logo) : ?>
                                <img src="<?php echo esc_url($home_logo); ?>" alt="">
                            <?php endif; ?>
                            <h1><?php echo wp_kses_post(football_fixture_team_link($home_team, $home_name)); ?></h1>
                        </div>

                        <div class="fixture-score">
                            <strong><?php echo esc_html(football_fixture_score($home_score, $away_score)); ?></strong>
                            <span><?php echo esc_html($status ?: '-'); ?></span>
                        </div>

                        <div class="fixture-team fixture-team--away">
                            <?php if ($away_logo) : ?>
                                <img src="<?php echo esc_url($away_logo); ?>" alt="">
                            <?php endif; ?>
                            <h2><?php echo wp_kses_post(football_fixture_team_link($away_team, $away_name)); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container fixture-layout">
            <div class="fixture-main">
                <article class="fixture-panel">
                    <h2><?php echo esc_html__('Информация матча', 'football'); ?></h2>
                    <dl class="fixture-facts">
                        <div><dt>API ID</dt><dd><?php echo esc_html($api_id ?: '-'); ?></dd></div>
                        <div><dt>Дата</dt><dd><?php echo esc_html(football_fixture_format_date($date)); ?></dd></div>
                        <div><dt>Статус</dt><dd><?php echo esc_html(trim($status . ($status_short ? ' · ' . $status_short : '')) ?: '-'); ?></dd></div>
                        <div><dt>Минута</dt><dd><?php echo esc_html($elapsed ? $elapsed . ($extra ? '+' . $extra : '') : '-'); ?></dd></div>
                        <div><dt>Судья</dt><dd><?php echo esc_html($referee ?: '-'); ?></dd></div>
                        <div><dt>Часовой пояс API</dt><dd><?php echo esc_html($timezone ?: '-'); ?></dd></div>
                        <div><dt>Timestamp</dt><dd><?php echo esc_html($timestamp ?: '-'); ?></dd></div>
                        <div><dt>Раунд</dt><dd><?php echo esc_html($round ?: '-'); ?></dd></div>
                    </dl>
                </article>

                <article class="fixture-panel">
                    <h2><?php echo esc_html__('Счет по периодам', 'football'); ?></h2>
                    <?php if ($score_payload) : ?>
                        <div class="fixture-periods">
                            <?php foreach ($score_payload as $label => $score) : ?>
                                <?php if (!is_array($score)) {
                                    continue;
                                } ?>
                                <div>
                                    <dt><?php echo esc_html((string) $label); ?></dt>
                                    <dd><?php echo esc_html(($score['home'] ?? '-') . ':' . ($score['away'] ?? '-')); ?></dd>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="fixture-muted">Детальный счет по периодам пока не сохранен.</p>
                    <?php endif; ?>

                    <?php if ($periods_payload) : ?>
                        <dl class="fixture-period-times">
                            <?php foreach ($periods_payload as $label => $value) : ?>
                                <div><dt><?php echo esc_html((string) $label); ?></dt><dd><?php echo esc_html(football_fixture_payload_value($value)); ?></dd></div>
                            <?php endforeach; ?>
                        </dl>
                    <?php endif; ?>
                </article>

                <article class="fixture-panel">
                    <h2><?php echo esc_html__('Статистика матча', 'football'); ?></h2>
                    <?php if ($statistics) : ?>
                        <div class="fixture-stats">
                            <?php foreach ($statistics as $row) : ?>
                                <?php
                                $home_value = (string) ($row['home'] ?? '');
                                $away_value = (string) ($row['away'] ?? '');
                                $home_number = football_fixture_stat_number($home_value);
                                $away_number = football_fixture_stat_number($away_value);
                                $total = ($home_number ?? 0) + ($away_number ?? 0);
                                $home_percent = $total > 0 ? max(0, min(100, (int) round((($home_number ?? 0) / $total) * 100))) : 50;
                                ?>
                                <div class="fixture-stat-row">
                                    <span><?php echo esc_html($home_value !== '' ? $home_value : '-'); ?></span>
                                    <div>
                                        <strong><?php echo esc_html($row['label'] ?? ''); ?></strong>
                                        <em><i style="width: <?php echo esc_attr((string) $home_percent); ?>%;"></i></em>
                                    </div>
                                    <span><?php echo esc_html($away_value !== '' ? $away_value : '-'); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="fixture-muted">Статистика появится после детальной загрузки матча.</p>
                    <?php endif; ?>
                </article>

                <article class="fixture-panel">
                    <h2><?php echo esc_html__('Лента событий', 'football'); ?></h2>
                    <?php if ($events) : ?>
                        <ol class="fixture-events">
                            <?php foreach ($events as $event) : ?>
                                <li>
                                    <span><?php echo esc_html(($event['minute'] ?? '') !== '' ? $event['minute'] . "'" : '-'); ?></span>
                                    <div>
                                        <strong><?php echo esc_html($event['player'] ?? ''); ?></strong>
                                        <small><?php echo esc_html(implode(' · ', array_filter([$event['team'] ?? '', $event['type'] ?? '', $event['description'] ?? '']))); ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php else : ?>
                        <p class="fixture-muted">События пока не загружены.</p>
                    <?php endif; ?>
                </article>
            </div>

            <aside class="fixture-side">
                <section class="fixture-panel">
                    <h2><?php echo esc_html__('Турнир', 'football'); ?></h2>
                    <dl class="fixture-side-list">
                        <div>
                            <dt>Турнир</dt>
                            <dd>
                                <?php if ($league) : ?>
                                    <a href="<?php echo esc_url(get_permalink($league)); ?>"><?php echo esc_html(get_the_title($league)); ?></a>
                                <?php else : ?>
                                    <?php echo esc_html($league_name ?: '-'); ?>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div><dt>Сезон</dt><dd><?php echo $league_season ? esc_html(get_the_title($league_season)) : '-'; ?></dd></div>
                        <div><dt>Раунд</dt><dd><?php echo esc_html($round ?: '-'); ?></dd></div>
                    </dl>
                </section>

                <section class="fixture-panel">
                    <h2><?php echo esc_html__('Стадион', 'football'); ?></h2>
                    <dl class="fixture-side-list">
                        <div>
                            <dt>Название</dt>
                            <dd>
                                <?php if ($venue) : ?>
                                    <a href="<?php echo esc_url(get_permalink($venue)); ?>"><?php echo esc_html(get_the_title($venue)); ?></a>
                                <?php else : ?>
                                    <?php echo esc_html($venue_name ?: '-'); ?>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div><dt>Город</dt><dd><?php echo esc_html($venue_city ?: ($venue ? get_post_meta($venue->ID, 'football_city', true) : '-')); ?></dd></div>
                    </dl>
                </section>

                <?php if ($odds) : ?>
                    <section class="fixture-panel">
                        <h2><?php echo esc_html__('Коэффициенты', 'football'); ?></h2>
                        <div class="fixture-odds">
                            <?php foreach ($odds as $odd) : ?>
                                <div>
                                    <strong><?php echo esc_html($odd['market'] ?? ''); ?></strong>
                                    <span><?php echo esc_html(implode(' / ', array_filter([$odd['home'] ?? '', $odd['draw'] ?? '', $odd['away'] ?? '']))); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </aside>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
