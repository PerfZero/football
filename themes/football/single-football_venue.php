<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

function football_venue_meta(string $key): mixed
{
    return get_post_meta(get_the_ID(), $key, true);
}

function football_venue_image_url(mixed $value): string
{
    if (is_numeric($value)) {
        return wp_get_attachment_image_url((int) $value, 'full') ?: '';
    }

    return esc_url_raw((string) $value);
}
?>

<main id="main" class="team-page">
    <?php while (have_posts()) : ?>
        <?php the_post(); ?>
        <?php
        $api_id = football_venue_meta('football_api_id');
        $address = football_venue_meta('football_venue_address');
        $city = football_venue_meta('football_city');
        $country = football_venue_meta('football_country');
        $capacity = football_venue_meta('football_venue_capacity');
        $surface = football_venue_meta('football_venue_surface');
        $image = football_venue_image_url(football_venue_meta('football_venue_image'));

        $teams = get_posts([
            'post_type' => 'football_team',
            'post_status' => 'publish',
            'posts_per_page' => 30,
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_key' => 'football_venue_post_id',
            'meta_value' => (string) get_the_ID(),
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
                    'key' => 'football_venue_post_id',
                    'value' => (string) get_the_ID(),
                ],
            ],
        ]);
        ?>

        <section class="team-hero">
            <div class="container team-hero__inner">
                <p class="breadcrumbs">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php football_esc_html_t('home'); ?></a>
                    <span>/</span>
                    <span><?php echo esc_html__('Стадион', 'football'); ?></span>
                </p>
                <div class="team-hero__title">
                    <?php if ($image) : ?>
                        <img src="<?php echo esc_url($image); ?>" alt="">
                    <?php endif; ?>
                    <div>
                        <p class="eyebrow">API ID <?php echo esc_html($api_id); ?></p>
                        <h1><?php the_title(); ?></h1>
                        <p><?php echo esc_html(implode(' · ', array_filter([$city, $country]))); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <section class="container team-layout">
            <div class="team-main">
                <article class="team-panel">
                    <h2><?php echo esc_html__('Информация стадиона', 'football'); ?></h2>
                    <dl class="team-facts">
                        <div><dt>Адрес</dt><dd><?php echo esc_html($address ?: '-'); ?></dd></div>
                        <div><dt>Город</dt><dd><?php echo esc_html($city ?: '-'); ?></dd></div>
                        <div><dt>Страна</dt><dd><?php echo esc_html($country ?: '-'); ?></dd></div>
                        <div><dt>Вместимость</dt><dd><?php echo esc_html($capacity ?: '-'); ?></dd></div>
                        <div><dt>Покрытие</dt><dd><?php echo esc_html($surface ?: '-'); ?></dd></div>
                    </dl>
                </article>

                <article class="team-panel">
                    <h2><?php echo esc_html__('Матчи на стадионе', 'football'); ?></h2>
                    <?php if ($fixtures) : ?>
                        <div class="team-fixtures">
                            <?php foreach ($fixtures as $fixture) : ?>
                                <a class="team-fixture" href="<?php echo esc_url(get_permalink($fixture)); ?>">
                                    <span><?php echo esc_html(get_post_meta($fixture->ID, 'football_match_datetime', true)); ?></span>
                                    <strong><?php echo esc_html(get_post_meta($fixture->ID, 'football_home_team', true)); ?></strong>
                                    <em>vs</em>
                                    <strong><?php echo esc_html(get_post_meta($fixture->ID, 'football_away_team', true)); ?></strong>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="team-muted">Матчи пока не привязаны к стадиону.</p>
                    <?php endif; ?>
                </article>
            </div>

            <aside class="team-side">
                <section class="team-panel">
                    <h2><?php echo esc_html__('Команды', 'football'); ?></h2>
                    <?php if ($teams) : ?>
                        <ul class="league-list">
                            <?php foreach ($teams as $team) : ?>
                                <li><a href="<?php echo esc_url(get_permalink($team)); ?>"><?php echo esc_html(get_the_title($team)); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <p class="team-muted">Команды пока не привязаны.</p>
                    <?php endif; ?>
                </section>
            </aside>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
