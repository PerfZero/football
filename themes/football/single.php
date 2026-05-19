<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="main" class="site-main">
    <section class="container single-post">
        <?php while (have_posts()) : ?>
            <?php the_post(); ?>
            <article <?php post_class(); ?>>
                <p class="post-card__meta"><?php echo esc_html(get_the_date()); ?></p>
                <h1><?php the_title(); ?></h1>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="single-post__image">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </section>
</main>

<?php
get_footer();
