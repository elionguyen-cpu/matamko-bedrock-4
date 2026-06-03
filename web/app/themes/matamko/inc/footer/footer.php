<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

add_action('init', 'matamko_register_footer_post_type');
add_action('add_meta_boxes_theme_footer', 'matamko_add_footer_metaboxes');
add_action('save_post_theme_footer', 'matamko_save_footer_post', 10, 1);
add_filter('manage_theme_footer_posts_columns', 'matamko_footer_columns');
add_action('manage_theme_footer_posts_custom_column', 'matamko_footer_column_content', 10, 2);

function matamko_register_footer_post_type(): void
{
    register_post_type(
        'theme_footer',
        [
            'labels' => [
                'name'          => esc_html__('Footers', 'matamko'),
                'singular_name' => esc_html__('Footer', 'matamko'),
                'add_new_item'  => esc_html__('Add New Footer', 'matamko'),
                'edit_item'     => esc_html__('Edit Footer', 'matamko'),
            ],
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => 'matamko-theme-builder',
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_in_rest'        => true,
            'supports'            => ['title'],
            'capability_type'     => 'page',
            'menu_position'       => 60,
        ],
    );
}

function matamko_add_footer_metaboxes(WP_Post $post): void
{
    add_meta_box(
        'matamko_footer_settings',
        esc_html__('Footer Settings', 'matamko'),
        'matamko_render_footer_metabox',
        'theme_footer',
        'side',
        'high',
    );
}

function matamko_render_footer_metabox(WP_Post $post): void
{
    matamko_render_active_checkbox_field($post, 'matamko_save_footer', 'matamko_footer_nonce');
}

function matamko_save_footer_post(int $post_id): void
{
    matamko_save_active_builder_status($post_id, 'theme_footer', 'matamko_save_footer', 'matamko_footer_nonce');
}

function matamko_footer_columns(array $columns): array
{
    $columns['matamko_active'] = esc_html__('Status', 'matamko');

    return $columns;
}

function matamko_footer_column_content(string $column, int $post_id): void
{
    if ($column === 'matamko_active') {
        matamko_render_active_state_column($post_id);
    }
}

function matamko_render_theme_footer(): bool
{
    if (matamko_is_elementor_editing_builder_post('theme_footer')) {
        return true;
    }

    $post_id = matamko_get_active_builder_post_id('theme_footer');

    if ($post_id <= 0) {
        return false;
    }

    $content = matamko_render_elementor_content($post_id);

    if ($content === '') {
        return false;
    }

    echo '<footer class="site-footer site-footer--elementor">';
    echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo '</footer>';

    return true;
}
