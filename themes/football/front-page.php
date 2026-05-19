<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$sections = [
    [
        'title' => football_t('section.leagues'),
        'post_type' => 'football_league',
        'archive_label' => football_t('archive.leagues'),
    ],
    [
        'title' => football_t('section.teams'),
        'post_type' => 'football_team',
        'archive_label' => football_t('archive.teams'),
    ],
    [
        'title' => football_t('section.players'),
        'post_type' => 'football_player',
        'archive_label' => football_t('archive.players'),
    ],
    [
        'title' => football_t('section.fixtures'),
        'post_type' => 'football_fixture',
        'archive_label' => football_t('archive.fixtures'),
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
        <p class="home-page__eyebrow"><?php football_esc_html_t('site.section.football'); ?></p>
        <h1><?php bloginfo('name'); ?></h1>
        <p>
            <?php football_esc_html_t('site.home.subtitle'); ?>
        </p>
    </section>

    <section class="container home-page__quick">
        <h2><?php football_esc_html_t('site.quick_links'); ?></h2>
        <div class="home-page__links">
            <?php foreach ($sections as $section) : ?>
                <?php
                $archive_url = $section['post_type'] === 'post'
                    ? get_permalink(get_option('page_for_posts'))
                    : get_post_type_archive_link($section['post_type']);
                ?>
                <?php if ($archive_url) : ?>
                    <a href="<?php echo esc_url($archive_url); ?>"><?php echo esc_html($section['archive_label']); ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="container home-page__grid">
        <?php foreach ($sections as $section) : ?>
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
                    <?php
                    $archive_url = $section['post_type'] === 'post'
                        ? get_permalink(get_option('page_for_posts'))
                        : get_post_type_archive_link($section['post_type']);
                    ?>
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
