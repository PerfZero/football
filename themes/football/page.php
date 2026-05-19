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
                <h1><?php the_title(); ?></h1>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </section>
</main>

<?php
get_footer();
