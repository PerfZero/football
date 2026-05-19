<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

function football_league_meta(string $key): mixed
{
    return get_post_meta(get_the_ID(), $key, true);
}

function football_league_json_meta(string $key): array
{
    $value = football_league_meta($key);
    if (is_array($value)) {
        return $value;
    }

    $decoded = json_decode((string) $value, true);

    return is_array($decoded) ? $decoded : [];
}

function football_league_image_url(mixed $value): string
{
    if (is_numeric($value)) {
        return wp_get_attachment_image_url((int) $value, 'full') ?: '';
    }

    return esc_url_raw((string) $value);
}

function football_league_bool_label(mixed $value): string
{
    return $value ? 'Да' : 'Нет';
}

function football_league_team_url(mixed $api_id): string
{
    $api_id = absint($api_id);
    if (!$api_id) {
        return '';
    }

    $teams = get_posts([
        'post_type' => 'football_team',
        'post_status' => 'publish',
        'fields' => 'ids',
        'posts_per_page' => 1,
        'meta_key' => 'football_api_id',
        'meta_value' => (string) $api_id,
    ]);

    return $teams ? get_permalink((int) $teams[0]) : '';
}
?>

<main id="main" class="league-page">
    <?php while (have_posts()) : ?>
        <?php the_post(); ?>
        <?php
        $api_id = football_league_meta('football_api_id');
        $logo = football_league_image_url(football_league_meta('football_logo'));
        $country = football_league_meta('football_country');
        $country_code = football_league_meta('football_country_code');
        $country_flag = football_league_image_url(football_league_meta('football_country_flag'));
        $season = football_league_meta('football_season');
        $season_start = football_league_meta('football_season_start');
        $season_end = football_league_meta('football_season_end');
        $season_current = football_league_meta('football_season_current');
        $type = football_league_meta('football_league_type');
        $standings = football_league_meta('football_standings');
        $standings = is_array($standings) ? $standings : [];

        $teams = get_posts([
            'post_type' => 'football_team',
            'post_status' => 'publish',
            'posts_per_page' => 30,
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_key' => 'football_league_api_id',
            'meta_value' => (string) $api_id,
        ]);

        $fixtures = get_posts([
            'post_type' => 'football_fixture',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_key' => 'football_match_datetime',
            'meta_query' => [
                [
                    'key' => 'football_league_api_id',
                    'value' => (string) $api_id,
                ],
            ],
        ]);
        ?>

        <section class="league-hero">
            <div class="container league-hero__inner">
                <div>
                    <p class="breadcrumbs">
                        <a href="<?php echo esc_url(home_url('/')); ?>"><?php football_esc_html_t('home'); ?></a>
                        <span>/</span>
                        <span><?php echo esc_html__('Турнир', 'football'); ?></span>
                    </p>
                    <div class="league-hero__title">
                        <?php if ($logo) : ?>
                            <img src="<?php echo esc_url($logo); ?>" alt="">
                        <?php endif; ?>
                        <div>
                            <p class="eyebrow"><?php echo esc_html($type ?: 'league'); ?> · API ID <?php echo esc_html($api_id); ?></p>
                            <h1><?php the_title(); ?></h1>
                            <p>
                                <?php if ($country_flag) : ?>
                                    <img class="league-hero__flag" src="<?php echo esc_url($country_flag); ?>" alt="">
                                <?php endif; ?>
                                <span><?php echo esc_html(trim($country . ($country_code ? ' · ' . $country_code : ''))); ?></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container league-layout">
            <div class="league-main">
                <article class="league-panel">
                    <h2><?php echo esc_html__('Информация турнира', 'football'); ?></h2>
                    <dl class="league-facts">
                        <div><dt>Сезон</dt><dd><?php echo esc_html($season ?: '-'); ?></dd></div>
                        <div><dt>Начало</dt><dd><?php echo esc_html($season_start ?: '-'); ?></dd></div>
                        <div><dt>Окончание</dt><dd><?php echo esc_html($season_end ?: '-'); ?></dd></div>
                        <div><dt>Текущий</dt><dd><?php echo esc_html(football_league_bool_label($season_current)); ?></dd></div>
                        <div><dt>Страна</dt><dd><?php echo esc_html($country ?: '-'); ?></dd></div>
                        <div><dt>Тип</dt><dd><?php echo esc_html($type ?: '-'); ?></dd></div>
                    </dl>

                    <?php if (trim(get_the_content()) !== '') : ?>
                        <div class="entry-content league-content">
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>
                </article>

                <article class="league-panel">
                    <h2><?php echo esc_html__('Турнирная таблица', 'football'); ?></h2>
                    <?php if ($standings) : ?>
                        <?php foreach ($standings as $group) : ?>
                            <div class="league-table-wrap">
                                <table class="league-table">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Команда</th>
                                        <th>И</th>
                                        <th>В</th>
                                        <th>Н</th>
                                        <th>П</th>
                                        <th>Г</th>
                                        <th>+/-</th>
                                        <th>О</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($group as $row) : ?>
                                        <tr>
                                            <td><?php echo esc_html($row['rank'] ?? ''); ?></td>
                                            <td>
                                                <?php
                                                $team_name = $row['team']['name'] ?? '';
                                                $team_url = football_league_team_url($row['team']['id'] ?? 0);
                                                ?>
                                                <<?php echo $team_url ? 'a' : 'span'; ?> class="league-team-cell" <?php echo $team_url ? 'href="' . esc_url($team_url) . '"' : ''; ?>>
                                                    <?php $team_logo = football_league_image_url($row['team']['logo'] ?? ''); ?>
                                                    <?php if ($team_logo) : ?>
                                                        <img src="<?php echo esc_url($team_logo); ?>" alt="">
                                                    <?php endif; ?>
                                                    <?php echo esc_html($team_name); ?>
                                                </<?php echo $team_url ? 'a' : 'span'; ?>>
                                            </td>
                                            <td><?php echo esc_html($row['all']['played'] ?? ''); ?></td>
                                            <td><?php echo esc_html($row['all']['win'] ?? ''); ?></td>
                                            <td><?php echo esc_html($row['all']['draw'] ?? ''); ?></td>
                                            <td><?php echo esc_html($row['all']['lose'] ?? ''); ?></td>
                                            <td><?php echo esc_html(($row['all']['goals']['for'] ?? '') . ':' . ($row['all']['goals']['against'] ?? '')); ?></td>
                                            <td><?php echo esc_html($row['goalsDiff'] ?? ''); ?></td>
                                            <td><strong><?php echo esc_html($row['points'] ?? ''); ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="league-muted">Таблица пока не загружена. В админке откройте Football Data → Синхронизация → Загрузить таблицы.</p>
                    <?php endif; ?>
                </article>
            </div>

            <aside class="league-side">
                <section class="league-panel">
                    <h2><?php echo esc_html__('Команды', 'football'); ?></h2>
                    <?php if ($teams) : ?>
                        <ul class="league-list">
                            <?php foreach ($teams as $team) : ?>
                                <li><a href="<?php echo esc_url(get_permalink($team)); ?>"><?php echo esc_html(get_the_title($team)); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <p class="league-muted">Команды пока не загружены.</p>
                    <?php endif; ?>
                </section>

                <section class="league-panel">
                    <h2><?php echo esc_html__('Матчи', 'football'); ?></h2>
                    <?php if ($fixtures) : ?>
                        <ul class="league-list">
                            <?php foreach ($fixtures as $fixture) : ?>
                                <li>
                                    <a href="<?php echo esc_url(get_permalink($fixture)); ?>"><?php echo esc_html(get_the_title($fixture)); ?></a>
                                    <small><?php echo esc_html(get_post_meta($fixture->ID, 'football_match_datetime', true)); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <p class="league-muted">Матчи пока не загружены.</p>
                    <?php endif; ?>
                </section>
            </aside>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
