<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<footer class="site-footer">
    <div class="container site-footer__inner">
        <p>&copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?></p>
        <a href="<?php echo esc_url(home_url('/')); ?>"><?php football_esc_html_t('site.back_home'); ?></a>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
