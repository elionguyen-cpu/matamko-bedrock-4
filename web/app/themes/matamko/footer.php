<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}
?>
<?php if (! matamko_render_theme_footer()) : ?>
    <footer class="site-footer site-footer--fallback">
        <div class="site-footer__inner">
            <p>&copy; <?php echo esc_html((string) gmdate('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?></p>
            <?php
            wp_nav_menu(
                [
                    'theme_location' => 'footer',
                    'container'      => 'nav',
                    'container_class' => 'site-footer-navigation',
                    'fallback_cb'    => false,
                ],
            );
    ?>
        </div>
    </footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
