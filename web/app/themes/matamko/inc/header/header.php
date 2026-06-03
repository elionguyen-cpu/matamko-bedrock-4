<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

add_action('init', 'matamko_register_header_post_type');
add_action('add_meta_boxes_theme_header', 'matamko_add_header_metaboxes');
add_action('save_post_theme_header', 'matamko_save_header_post', 10, 1);
add_filter('manage_theme_header_posts_columns', 'matamko_header_columns');
add_action('manage_theme_header_posts_custom_column', 'matamko_header_column_content', 10, 2);

function matamko_register_header_post_type(): void
{
    register_post_type(
        'theme_header',
        [
            'labels' => [
                'name'          => esc_html__('Headers', 'matamko'),
                'singular_name' => esc_html__('Header', 'matamko'),
                'add_new_item'  => esc_html__('Add New Header', 'matamko'),
                'edit_item'     => esc_html__('Edit Header', 'matamko'),
            ],
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => 'matamko-theme-builder',
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'has_archive'         => false,
            'rewrite'             => false,
            'query_var'           => true,
            'show_in_rest'        => true,
            'supports'            => ['title'],
            'capability_type'     => 'page',
            'menu_position'       => 59,
        ],
    );
}

function matamko_add_header_metaboxes(WP_Post $post): void
{
    add_meta_box(
        'matamko_header_settings',
        esc_html__('Header Settings', 'matamko'),
        'matamko_render_header_metabox',
        'theme_header',
        'side',
        'high',
    );
}

function matamko_render_header_metabox(WP_Post $post): void
{
    matamko_render_active_checkbox_field($post, 'matamko_save_header', 'matamko_header_nonce');
}

function matamko_save_header_post(int $post_id): void
{
    matamko_save_active_builder_status($post_id, 'theme_header', 'matamko_save_header', 'matamko_header_nonce');
}

function matamko_header_columns(array $columns): array
{
    $columns['matamko_active'] = esc_html__('Status', 'matamko');

    return $columns;
}

function matamko_header_column_content(string $column, int $post_id): void
{
    if ($column === 'matamko_active') {
        matamko_render_active_state_column($post_id);
    }
}

function matamko_render_theme_header(): bool
{
    if (matamko_is_elementor_editing_builder_post('theme_header')) {
        return true;
    }

    $post_id = matamko_get_active_builder_post_id('theme_header');

    if ($post_id <= 0) {
        return false;
    }

    $content = matamko_render_elementor_content($post_id);

    if ($content === '') {
        return false;
    }

    echo '<header class="site-header site-header--elementor">';
    echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo '</header>';

    return true;
}
