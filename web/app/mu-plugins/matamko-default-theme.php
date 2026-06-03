<?php

declare(strict_types=1);

/**
 * Plugin Name: MATAMKO Default Theme
 * Description: Keeps the Bedrock-configured default theme active.
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('wp_loaded', 'matamko_maybe_activate_default_theme');

function matamko_maybe_activate_default_theme(): void
{
    if (! defined('WP_DEFAULT_THEME') || WP_DEFAULT_THEME === '') {
        return;
    }

    $default_theme = sanitize_key((string) WP_DEFAULT_THEME);

    if ($default_theme === '' || get_stylesheet() === $default_theme) {
        return;
    }

    $theme = wp_get_theme($default_theme);

    if (! $theme->exists()) {
        return;
    }

    switch_theme($default_theme);
}
