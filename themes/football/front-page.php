<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

function football_home_archive_url(string $post_type): string
{
    $url = $post_type === 'post'
        ? get_permalink(get_option('page_for_posts'))
        : get_post_type_archive_link($post_type);

    return $url ? (string) $url : '';
}

function football_home_image_url(mixed $value): string
{
    if (is_numeric($value)) {
        return wp_get_attachment_image_url((int) $value, 'thumbnail') ?: '';
    }

    return esc_url_raw((string) $value);
}

function football_home_meta(int $post_id, string $key): mixed
{
    return get_post_meta($post_id, $key, true);
}

function football_home_linked_post(mixed $post_id): ?WP_Post
{
    $post_id = absint($post_id);
    if (!$post_id) {
        return null;
    }

    $post = get_post($post_id);

    return $post instanceof WP_Post ? $post : null;
}

function football_home_related_count(string $post_type, string $meta_key, mixed $value): int
{
    if ($value === '' || $value === null) {
        return 0;
    }

    $query = new WP_Query([
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => $meta_key,
        'meta_value' => (string) $value,
    ]);

    return (int) $query->found_posts;
}

function football_home_format_date(string $value): string
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

function football_home_match_score(string $home_score, string $away_score): string
{
    if ($home_score !== '' || $away_score !== '') {
        return ($home_score !== '' ? $home_score : '-') . ':' . ($away_score !== '' ? $away_score : '-');
    }

    return 'vs';
}

$leagues = get_posts([
    'post_type' => 'football_league',
    'post_status' => 'publish',
    'posts_per_page' => 4,
    'orderby' => 'date',
    'order' => 'DESC',
]);

$teams = get_posts([
    'post_type' => 'football_team',
    'post_status' => 'publish',
    'posts_per_page' => 6,
    'orderby' => 'date',
    'order' => 'DESC',
]);

$fixtures = get_posts([
    'post_type' => 'football_fixture',
    'post_status' => 'publish',
    'posts_per_page' => 6,
    'orderby' => 'meta_value',
    'order' => 'DESC',
    'meta_key' => 'football_match_datetime',
]);

$compact_sections = [
    [
        'title' => football_t('section.players'),
        'post_type' => 'football_player',
        'archive_label' => football_t('archive.players'),
    ],
    [
        'title' => football_t('section.bookmakers'),
        'post_type' => 'football_bookmaker',
        'archive_label' => football_t('archive.bookmakers'),
    ],
    [
        'title' => football_t('section.news'),
        'post_type' => 'post',
        'archive_label' => football_t('archive.news'),
    ],
];
?>

<main id="main" class="site-main home-page">
    <section class="container home-page__intro">
        <div>
            <p class="home-page__eyebrow"><?php football_esc_html_t('site.section.football'); ?></p>
            <h1><?php bloginfo('name'); ?></h1>
            <p>
                <?php football_esc_html_t('site.home.subtitle'); ?>
            </p>
        </div>
    </section>

    <section class="container home-featured">
        <header class="home-section-heading">
            <h2><?php football_esc_html_t('home.featured_leagues'); ?></h2>
        </header>

        <?php if ($leagues) : ?>
            <div class="home-league-grid">
                <?php foreach ($leagues as $league) : ?>
                    <?php
                    $league_id = $league->ID;
                    $league_api_id = football_home_meta($league_id, 'football_api_id');
                    $logo = football_home_image_url(football_home_meta($league_id, 'football_logo'));
                    $flag = football_home_image_url(football_home_meta($league_id, 'football_country_flag'));
                    $country = football_home_meta($league_id, 'football_country');
                    $season = football_home_meta($league_id, 'football_season');
                    $type = football_home_meta($league_id, 'football_league_type');
                    $team_count = football_home_related_count('football_team', 'football_league_api_id', $league_api_id);
                    $fixture_count = football_home_related_count('football_fixture', 'football_league_api_id', $league_api_id);
                    ?>
                    <article class="home-league-card">
                        <a class="home-league-card__top" href="<?php echo esc_url(get_permalink($league)); ?>">
                            <span class="home-league-card__logo">
                                <?php if ($logo) : ?>
                                    <img src="<?php echo esc_url($logo); ?>" alt="">
                                <?php endif; ?>
                            </span>
                            <span>
                                <span class="home-card-kicker"><?php echo esc_html($type ?: football_t('section.leagues')); ?></span>
                                <strong><?php echo esc_html(get_the_title($league)); ?></strong>
                            </span>
                        </a>
                        <dl class="home-data-list">
                            <div>
                                <dt><?php football_esc_html_t('home.season'); ?></dt>
                                <dd><?php echo esc_html($season ?: football_t('home.not_set')); ?></dd>
                            </div>
                            <div>
                                <dt><?php football_esc_html_t('home.country'); ?></dt>
                                <dd>
                                    <?php if ($flag) : ?>
                                        <img src="<?php echo esc_url($flag); ?>" alt="">
                                    <?php endif; ?>
                                    <?php echo esc_html($country ?: football_t('home.not_set')); ?>
                                </dd>
                            </div>
                            <div>
                                <dt><?php football_esc_html_t('home.teams'); ?></dt>
                                <dd><?php echo esc_html((string) $team_count); ?></dd>
                            </div>
                            <div>
                                <dt><?php football_esc_html_t('home.matches'); ?></dt>
                                <dd><?php echo esc_html((string) $fixture_count); ?></dd>
                            </div>
                        </dl>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="home-empty"><?php football_esc_html_t('site.no_posts'); ?></p>
        <?php endif; ?>
    </section>

    <section class="container home-featured">
        <header class="home-section-heading">
            <h2><?php football_esc_html_t('home.featured_teams'); ?></h2>
        </header>

        <?php if ($teams) : ?>
            <div class="home-team-grid">
                <?php foreach ($teams as $team) : ?>
                    <?php
                    $team_id = $team->ID;
                    $logo = football_home_image_url(football_home_meta($team_id, 'football_logo'));
                    $country = football_home_meta($team_id, 'football_country');
                    $founded = football_home_meta($team_id, 'football_founded');
                    $league = football_home_linked_post(football_home_meta($team_id, 'football_league_post_id'));
                    $venue = football_home_linked_post(football_home_meta($team_id, 'football_venue_post_id'));
                    $stadium = $venue ? get_the_title($venue) : football_home_meta($team_id, 'football_stadium');
                    ?>
                    <article class="home-team-card">
                        <a class="home-team-card__title" href="<?php echo esc_url(get_permalink($team)); ?>">
                            <span class="home-team-card__logo">
                                <?php if ($logo) : ?>
                                    <img src="<?php echo esc_url($logo); ?>" alt="">
                                <?php endif; ?>
                            </span>
                            <span>
                                <strong><?php echo esc_html(get_the_title($team)); ?></strong>
                                <small><?php echo esc_html($country ?: football_t('home.not_set')); ?></small>
                            </span>
                        </a>
                        <dl class="home-data-list home-data-list--compact">
                            <div>
                                <dt><?php football_esc_html_t('home.league'); ?></dt>
                                <dd>
                                    <?php if ($league) : ?>
                                        <a href="<?php echo esc_url(get_permalink($league)); ?>"><?php echo esc_html(get_the_title($league)); ?></a>
                                    <?php else : ?>
                                        <?php echo esc_html(football_t('home.not_set')); ?>
                                    <?php endif; ?>
                                </dd>
                            </div>
                            <div>
                                <dt><?php football_esc_html_t('home.stadium'); ?></dt>
                                <dd>
                                    <?php if ($venue) : ?>
                                        <a href="<?php echo esc_url(get_permalink($venue)); ?>"><?php echo esc_html($stadium); ?></a>
                                    <?php else : ?>
                                        <?php echo esc_html($stadium ?: football_t('home.not_set')); ?>
                                    <?php endif; ?>
                                </dd>
                            </div>
                            <div>
                                <dt><?php football_esc_html_t('home.founded'); ?></dt>
                                <dd><?php echo esc_html($founded ?: football_t('home.not_set')); ?></dd>
                            </div>
                        </dl>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="home-empty"><?php football_esc_html_t('site.no_posts'); ?></p>
        <?php endif; ?>
    </section>

    <section class="container home-featured">
        <header class="home-section-heading">
            <h2><?php football_esc_html_t('home.featured_matches'); ?></h2>
        </header>

        <?php if ($fixtures) : ?>
            <div class="home-match-grid">
                <?php foreach ($fixtures as $fixture) : ?>
                    <?php
                    $fixture_id = $fixture->ID;
                    $league = football_home_linked_post(football_home_meta($fixture_id, 'football_league_post_id'));
                    $venue = football_home_linked_post(football_home_meta($fixture_id, 'football_venue_post_id'));
                    $home_team = football_home_linked_post(football_home_meta($fixture_id, 'football_home_team_post_id'));
                    $away_team = football_home_linked_post(football_home_meta($fixture_id, 'football_away_team_post_id'));
                    $home_name = football_home_meta($fixture_id, 'football_home_team');
                    $away_name = football_home_meta($fixture_id, 'football_away_team');
                    $home_logo = $home_team ? football_home_image_url(football_home_meta($home_team->ID, 'football_logo')) : '';
                    $away_logo = $away_team ? football_home_image_url(football_home_meta($away_team->ID, 'football_logo')) : '';
                    $date = football_home_meta($fixture_id, 'football_match_datetime');
                    $round = football_home_meta($fixture_id, 'football_round');
                    $status = football_home_meta($fixture_id, 'football_status');
                    $home_score = (string) football_home_meta($fixture_id, 'football_home_score');
                    $away_score = (string) football_home_meta($fixture_id, 'football_away_score');
                    ?>
                    <article class="home-match-card">
                        <div class="home-match-card__meta">
                            <span><?php echo esc_html(football_home_format_date($date)); ?></span>
                            <span><?php echo esc_html($league ? get_the_title($league) : football_home_meta($fixture_id, 'football_league_name')); ?></span>
                        </div>

                        <div class="home-match-card__teams">
                            <div class="home-match-team">
                                <?php if ($home_logo) : ?>
                                    <img src="<?php echo esc_url($home_logo); ?>" alt="">
                                <?php endif; ?>
                                <?php if ($home_team) : ?>
                                    <a href="<?php echo esc_url(get_permalink($home_team)); ?>"><?php echo esc_html(get_the_title($home_team)); ?></a>
                                <?php else : ?>
                                    <span><?php echo esc_html($home_name); ?></span>
                                <?php endif; ?>
                            </div>

                            <a class="home-match-score" href="<?php echo esc_url(get_permalink($fixture)); ?>">
                                <?php echo esc_html(football_home_match_score($home_score, $away_score)); ?>
                            </a>

                            <div class="home-match-team">
                                <?php if ($away_logo) : ?>
                                    <img src="<?php echo esc_url($away_logo); ?>" alt="">
                                <?php endif; ?>
                                <?php if ($away_team) : ?>
                                    <a href="<?php echo esc_url(get_permalink($away_team)); ?>"><?php echo esc_html(get_the_title($away_team)); ?></a>
                                <?php else : ?>
                                    <span><?php echo esc_html($away_name); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <dl class="home-match-card__facts">
                            <div>
                                <dt><?php football_esc_html_t('home.round'); ?></dt>
                                <dd><?php echo esc_html($round ?: football_t('home.not_set')); ?></dd>
                            </div>
                            <div>
                                <dt><?php football_esc_html_t('home.status'); ?></dt>
                                <dd><?php echo esc_html($status ?: football_t('home.not_set')); ?></dd>
                            </div>
                            <div>
                                <dt><?php football_esc_html_t('home.stadium'); ?></dt>
                                <dd>
                                    <?php if ($venue) : ?>
                                        <a href="<?php echo esc_url(get_permalink($venue)); ?>"><?php echo esc_html(get_the_title($venue)); ?></a>
                                    <?php else : ?>
                                        <?php echo esc_html(football_home_meta($fixture_id, 'football_venue') ?: football_t('home.not_set')); ?>
                                    <?php endif; ?>
                                </dd>
                            </div>
                        </dl>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="home-empty"><?php football_esc_html_t('site.no_posts'); ?></p>
        <?php endif; ?>
    </section>

    <section class="container home-page__grid">
        <header class="home-section-heading home-section-heading--full">
            <h2><?php football_esc_html_t('home.more_sections'); ?></h2>
        </header>

        <?php foreach ($compact_sections as $section) : ?>
            <?php
            $query = new WP_Query([
                'post_type' => $section['post_type'],
                'posts_per_page' => 6,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
                'ignore_sticky_posts' => true,
            ]);
            ?>

            <article class="home-card">
                <header class="home-card__header">
                    <h2><?php echo esc_html($section['title']); ?></h2>
                    <?php $archive_url = football_home_archive_url($section['post_type']); ?>
                    <?php if ($archive_url) : ?>
                        <a href="<?php echo esc_url($archive_url); ?>"><?php football_esc_html_t('site.archive'); ?></a>
                    <?php endif; ?>
                </header>

                <?php if ($query->have_posts()) : ?>
                    <ul class="home-list">
                        <?php while ($query->have_posts()) : ?>
                            <?php $query->the_post(); ?>
                            <li>
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                <?php if (has_excerpt()) : ?>
                                    <small><?php echo esc_html(get_the_excerpt()); ?></small>
                                <?php elseif (get_post_type() === 'post') : ?>
                                    <small><?php echo esc_html(get_the_date()); ?></small>
                                <?php elseif (get_post_type() === 'football_fixture') : ?>
                                    <small><?php echo esc_html(football_home_format_date(get_post_meta(get_the_ID(), 'football_match_datetime', true))); ?></small>
                                <?php else : ?>
                                    <small><?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_content()), 12)); ?></small>
                                <?php endif; ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else : ?>
                    <p class="home-empty"><?php football_esc_html_t('site.no_posts'); ?></p>
                <?php endif; ?>

                <?php wp_reset_postdata(); ?>
            </article>
        <?php endforeach; ?>
    </section>
</main>

<?php
get_footer();
