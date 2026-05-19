<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php football_esc_html_t('site.skip_to_content'); ?></a>

<header class="site-header">
    <div class="container site-header__inner">
        <div class="site-branding">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <a class="site-title" href="<?php echo esc_url(home_url('/')); ?>">
                    <?php bloginfo('name'); ?>
                </a>
            <?php endif; ?>
            <p class="site-description"><?php bloginfo('description'); ?></p>
        </div>

        <nav class="primary-nav" aria-label="<?php football_esc_attr_t('site.primary_navigation'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container' => false,
                'fallback_cb' => false,
                'menu_class' => 'primary-nav__menu',
            ]);
            ?>
        </nav>

        <?php if (function_exists('pll_the_languages')) : ?>
            <?php
            $football_languages = pll_the_languages([
                    'display_names_as' => 'slug',
                    'echo' => 0,
                    'hide_current' => 0,
                    'show_flags' => 0,
                    'show_names' => 1,
                    'dropdown' => 0,
                ]);
            ?>
            <?php if ($football_languages) : ?>
                <nav class="language-switcher" aria-label="<?php football_esc_attr_t('site.language_switcher'); ?>">
                    <ul>
                        <?php echo wp_kses_post($football_languages); ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</header>
