<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="main" class="site-main news-single">
    <?php while (have_posts()) : ?>
        <?php the_post(); ?>

        <section class="single-post-hero">
            <div class="container single-post-hero__inner">
                <p class="breadcrumbs">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php football_esc_html_t('home'); ?></a>
                    <span>/</span>
                    <span><?php football_esc_html_t('section.news'); ?></span>
                </p>
                <p class="post-card__meta"><?php echo esc_html(get_the_date()); ?></p>
                <h1><?php the_title(); ?></h1>
            </div>
        </section>

        <section class="container single-post-layout">
            <article <?php post_class('single-post-panel'); ?>>
                <?php if (has_post_thumbnail()) : ?>
                    <div class="single-post__image">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
