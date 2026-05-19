<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

function football_bookmaker_meta(string $key): mixed
{
    return get_post_meta(get_the_ID(), $key, true);
}

function football_bookmaker_image_url(mixed $value): string
{
    if (is_numeric($value)) {
        return wp_get_attachment_image_url((int) $value, 'full') ?: '';
    }

    return esc_url_raw((string) $value);
}

function football_bookmaker_rating(mixed $value): string
{
    if ($value === '' || $value === null) {
        return '-';
    }

    return is_numeric($value) ? number_format((float) $value, 1, '.', '') : (string) $value;
}
?>

<main id="main" class="bookmaker-page">
    <?php while (have_posts()) : ?>
        <?php the_post(); ?>
        <?php
        $logo = football_bookmaker_image_url(football_bookmaker_meta('football_logo'));
        $rating = football_bookmaker_meta('football_rating');
        $bonus = football_bookmaker_meta('football_bonus');
        $min_deposit = football_bookmaker_meta('football_min_deposit');
        $countries = football_bookmaker_meta('football_countries');
        $affiliate_url = football_bookmaker_meta('football_affiliate_url');
        $summary = football_bookmaker_meta('football_review_summary');
        $features = football_bookmaker_meta('football_features');
        $features = is_array($features) ? $features : [];
        ?>

        <section class="bookmaker-hero">
            <div class="container bookmaker-hero__inner">
                <p class="breadcrumbs">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php football_esc_html_t('home'); ?></a>
                    <span>/</span>
                    <span><?php football_esc_html_t('bookmaker.page_label'); ?></span>
                </p>

                <div class="bookmaker-hero__body">
                    <div class="bookmaker-hero__brand">
                        <span class="bookmaker-hero__logo">
                            <?php if ($logo) : ?>
                                <img src="<?php echo esc_url($logo); ?>" alt="">
                            <?php else : ?>
                                <?php echo esc_html(function_exists('mb_substr') ? mb_substr(get_the_title(), 0, 1) : substr(get_the_title(), 0, 1)); ?>
                            <?php endif; ?>
                        </span>
                        <div>
                            <p class="eyebrow"><?php football_esc_html_t('bookmaker.page_label'); ?></p>
                            <h1><?php the_title(); ?></h1>
                            <?php if ($summary) : ?>
                                <p><?php echo esc_html($summary); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bookmaker-hero__score">
                        <small><?php football_esc_html_t('home.rating'); ?></small>
                        <strong><?php echo esc_html(football_bookmaker_rating($rating)); ?></strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="container bookmaker-layout">
            <div class="bookmaker-main">
                <article class="bookmaker-panel">
                    <h2><?php football_esc_html_t('bookmaker.summary'); ?></h2>
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </article>

                <article class="bookmaker-panel">
                    <h2><?php football_esc_html_t('bookmaker.features'); ?></h2>
                    <?php if ($features) : ?>
                        <div class="bookmaker-features">
                            <?php foreach ($features as $feature) : ?>
                                <?php
                                $feature_title = is_array($feature) ? (string) ($feature['title'] ?? '') : '';
                                $feature_description = is_array($feature) ? (string) ($feature['description'] ?? '') : '';
                                ?>
                                <?php if ($feature_title || $feature_description) : ?>
                                    <div class="bookmaker-feature">
                                        <?php if ($feature_title) : ?>
                                            <h3><?php echo esc_html($feature_title); ?></h3>
                                        <?php endif; ?>
                                        <?php if ($feature_description) : ?>
                                            <p><?php echo esc_html($feature_description); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="bookmaker-muted"><?php football_esc_html_t('bookmaker.no_features'); ?></p>
                    <?php endif; ?>
                </article>
            </div>

            <aside class="bookmaker-side">
                <section class="bookmaker-panel">
                    <h2><?php football_esc_html_t('bookmaker.terms'); ?></h2>
                    <dl class="bookmaker-facts">
                        <div><dt><?php football_esc_html_t('home.rating'); ?></dt><dd><?php echo esc_html(football_bookmaker_rating($rating)); ?></dd></div>
                        <div><dt><?php football_esc_html_t('home.bonus'); ?></dt><dd><?php echo esc_html($bonus ?: football_t('home.not_set')); ?></dd></div>
                        <div><dt><?php football_esc_html_t('home.min_deposit'); ?></dt><dd><?php echo esc_html($min_deposit ?: football_t('home.not_set')); ?></dd></div>
                        <div><dt><?php football_esc_html_t('home.countries'); ?></dt><dd><?php echo esc_html($countries ?: football_t('home.not_set')); ?></dd></div>
                    </dl>

                    <?php if ($affiliate_url) : ?>
                        <a class="bookmaker-cta" href="<?php echo esc_url($affiliate_url); ?>" target="_blank" rel="nofollow sponsored noopener">
                            <?php football_esc_html_t('bookmaker.open_site'); ?>
                        </a>
                    <?php endif; ?>
                </section>
            </aside>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
