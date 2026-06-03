<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', 'matamko_theme_setup');
add_action('wp_enqueue_scripts', 'matamko_enqueue_assets');
add_action('admin_menu', 'matamko_register_theme_builder_menu');
add_action('admin_menu', 'matamko_register_theme_settings_page');
add_action('after_switch_theme', 'matamko_activate_supported_elementor_post_types');
add_action('admin_init', 'matamko_activate_supported_elementor_post_types');

function matamko_theme_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support(
        'html5',
        [
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        ],
    );
    add_theme_support('menus');
    add_theme_support('woocommerce');

    register_nav_menus(
        [
            'primary' => esc_html__('Primary Menu', 'matamko'),
            'footer'  => esc_html__('Footer Menu', 'matamko'),
        ],
    );
}

function matamko_enqueue_assets(): void
{
    $css_path = get_template_directory() . '/assets/css/main.css';
    $js_path  = get_template_directory() . '/assets/js/main.js';

    wp_enqueue_style(
        'matamko-main',
        get_template_directory_uri() . '/assets/css/main.css',
        [],
        file_exists($css_path) ? (string) filemtime($css_path) : wp_get_theme()->get('Version'),
    );

    wp_enqueue_script(
        'matamko-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        file_exists($js_path) ? (string) filemtime($js_path) : wp_get_theme()->get('Version'),
        true,
    );
}

function matamko_register_theme_builder_menu(): void
{
    add_menu_page(
        esc_html__('Theme Builder', 'matamko'),
        esc_html__('Theme Builder', 'matamko'),
        'edit_pages',
        'matamko-theme-builder',
        'matamko_render_theme_builder_page',
        'dashicons-layout',
        58,
    );
}

function matamko_register_theme_settings_page(): void
{
    add_theme_page(
        esc_html__('MATAMKO Settings', 'matamko'),
        esc_html__('MATAMKO Settings', 'matamko'),
        'manage_options',
        'matamko-settings',
        'matamko_render_theme_settings_page',
    );
}

function matamko_render_theme_builder_page(): void
{
    if (! current_user_can('edit_pages')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'matamko'));
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Theme Builder', 'matamko') . '</h1>';
    echo '<p>' . esc_html__('Manage global headers, footers, and content layouts from the submenu.', 'matamko') . '</p>';
    echo '</div>';
}

function matamko_render_theme_settings_page(): void
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'matamko'));
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('MATAMKO Settings', 'matamko') . '</h1>';
    echo '<p>' . esc_html__('This theme is configured for Bedrock, Elementor, and native WordPress builder content.', 'matamko') . '</p>';
    echo '</div>';
}

function matamko_activate_supported_elementor_post_types(): void
{
    $post_types = get_option('elementor_cpt_support');

    if (! is_array($post_types)) {
        $post_types = ['page', 'post'];
    }

    foreach (['theme_header', 'theme_footer', 'theme_layout'] as $post_type) {
        if (! in_array($post_type, $post_types, true)) {
            $post_types[] = $post_type;
        }
    }

    update_option('elementor_cpt_support', array_values($post_types));
}

function matamko_is_elementor_loaded(): bool
{
    return did_action('elementor/loaded') > 0 && class_exists('\Elementor\Plugin');
}

function matamko_render_elementor_content(int $post_id): string
{
    if ($post_id <= 0 || ! matamko_is_elementor_loaded()) {
        return '';
    }

    return (string) \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($post_id);
}

function matamko_get_elementor_edited_post_id(): int
{
    $candidate_keys = ['post', 'post_id', 'editor_post_id'];

    foreach ($candidate_keys as $key) {
        if (isset($_GET[$key])) {
            return absint(wp_unslash($_GET[$key]));
        }

        if (isset($_POST[$key])) {
            return absint(wp_unslash($_POST[$key]));
        }
    }

    if (isset($_REQUEST['elementor-preview'])) {
        return absint(wp_unslash($_REQUEST['elementor-preview']));
    }

    return 0;
}

function matamko_is_elementor_editing_builder_post(string $post_type): bool
{
    $post_id = matamko_get_elementor_edited_post_id();

    if ($post_id <= 0 || get_post_type($post_id) !== $post_type) {
        return false;
    }

    $is_admin_editor = is_admin()
        && isset($_GET['action'])
        && sanitize_key(wp_unslash($_GET['action'])) === 'elementor';

    $is_preview = isset($_REQUEST['elementor-preview']);

    return $is_admin_editor || $is_preview;
}

function matamko_get_active_builder_post_id(string $post_type): int
{
    $posts = get_posts(
        [
            'post_type'              => $post_type,
            'post_status'            => 'publish',
            'posts_per_page'         => 1,
            'fields'                 => 'ids',
            'meta_key'               => '_matamko_is_active',
            'meta_value'             => '1',
            'orderby'                => 'date',
            'order'                  => 'DESC',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ],
    );

    return isset($posts[0]) ? (int) $posts[0] : 0;
}

function matamko_render_active_checkbox_field(WP_Post $post, string $nonce_action, string $nonce_name): void
{
    wp_nonce_field($nonce_action, $nonce_name);

    $is_active = get_post_meta($post->ID, '_matamko_is_active', true) === '1';

    echo '<p>';
    echo '<label>';
    echo '<input type="checkbox" name="matamko_is_active" value="1" ' . checked($is_active, true, false) . '> ';
    echo esc_html__('Set as active', 'matamko');
    echo '</label>';
    echo '</p>';
}

function matamko_save_active_builder_status(int $post_id, string $post_type, string $nonce_action, string $nonce_name): void
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id) || get_post_type($post_id) !== $post_type) {
        return;
    }

    if (! isset($_POST[$nonce_name]) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$nonce_name])), $nonce_action)) {
        return;
    }

    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    $is_active = isset($_POST['matamko_is_active']) && sanitize_text_field(wp_unslash($_POST['matamko_is_active'])) === '1';

    update_post_meta($post_id, '_matamko_is_active', $is_active ? '1' : '0');

    if (! $is_active) {
        return;
    }

    $active_posts = get_posts(
        [
            'post_type'      => $post_type,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'post__not_in'   => [$post_id],
            'meta_key'       => '_matamko_is_active',
            'meta_value'     => '1',
        ],
    );

    foreach ($active_posts as $active_post_id) {
        update_post_meta((int) $active_post_id, '_matamko_is_active', '0');
    }
}

function matamko_render_active_state_column(int $post_id): void
{
    if (get_post_meta($post_id, '_matamko_is_active', true) === '1') {
        echo '<strong>' . esc_html__('Active', 'matamko') . '</strong>';
        return;
    }

    echo esc_html__('Inactive', 'matamko');
}
