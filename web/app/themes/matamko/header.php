<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if (! matamko_render_theme_header()) : ?>
    <header class="site-header site-header--fallback">
        <div class="site-header__inner">
            <?php
            if (has_custom_logo()) {
                the_custom_logo();
            } else {
                echo '<a class="site-title" href="' . esc_url(home_url('/')) . '">' . esc_html(get_bloginfo('name')) . '</a>';
            }

wp_nav_menu(
    [
        'theme_location' => 'primary',
        'container'      => 'nav',
        'container_class' => 'site-navigation',
        'fallback_cb'    => false,
    ],
);
?>
        </div>
    </header>
<?php endif; ?>
