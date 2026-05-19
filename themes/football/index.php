<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="main" class="site-main">
    <section class="page-hero">
        <div class="container page-hero__inner">
            <p class="eyebrow"><?php esc_html_e('Football Club', 'football'); ?></p>
            <h1><?php bloginfo('name'); ?></h1>
            <p><?php bloginfo('description'); ?></p>
        </div>
    </section>

    <section class="container content-grid">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : ?>
                <?php the_post(); ?>
                <article <?php post_class('post-card'); ?>>
                    <?php if (has_post_thumbnail()) : ?>
                        <a class="post-card__image" href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('large'); ?>
                        </a>
                    <?php endif; ?>

                    <div class="post-card__body">
                        <p class="post-card__meta"><?php echo esc_html(get_the_date()); ?></p>
                        <h2>
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <?php the_excerpt(); ?>
                        <a class="text-link" href="<?php the_permalink(); ?>">
                            <?php esc_html_e('Read more', 'football'); ?>
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>

            <div class="pagination">
                <?php the_posts_pagination(); ?>
            </div>
        <?php else : ?>
            <article class="empty-state">
                <h2><?php esc_html_e('No posts yet', 'football'); ?></h2>
                <p><?php esc_html_e('Create your first post in the WordPress admin area.', 'football'); ?></p>
            </article>
        <?php endif; ?>
    </section>
</main>

<?php
get_footer();
