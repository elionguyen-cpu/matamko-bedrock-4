<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

add_action('init', 'matamko_register_layout_post_type');
add_action('add_meta_boxes_theme_layout', 'matamko_add_layout_metaboxes');
add_action('save_post_theme_layout', 'matamko_save_layout_post', 10, 1);
add_filter('manage_theme_layout_posts_columns', 'matamko_layout_columns');
add_action('manage_theme_layout_posts_custom_column', 'matamko_layout_column_content', 10, 2);
add_filter('template_include', 'matamko_layout_override', 999);

function matamko_register_layout_post_type(): void
{
    register_post_type(
        'theme_layout',
        [
            'labels' => [
                'name'          => esc_html__('Layouts', 'matamko'),
                'singular_name' => esc_html__('Layout', 'matamko'),
                'add_new_item'  => esc_html__('Add New Layout', 'matamko'),
                'edit_item'     => esc_html__('Edit Layout', 'matamko'),
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
            'menu_position'       => 61,
        ],
    );
}

function matamko_add_layout_metaboxes(WP_Post $post): void
{
    add_meta_box(
        'matamko_layout_settings',
        esc_html__('Layout Settings', 'matamko'),
        'matamko_render_layout_metabox',
        'theme_layout',
        'normal',
        'high',
    );
}

function matamko_layout_location_types(): array
{
    $locations = [
        'home'          => esc_html__('Home', 'matamko'),
        'blog'          => esc_html__('Blog', 'matamko'),
        'single_post'   => esc_html__('Single Post', 'matamko'),
        'single_cpt'    => esc_html__('Single Custom Post Type', 'matamko'),
        'category'      => esc_html__('Category', 'matamko'),
        'taxonomy'      => esc_html__('Taxonomy Term', 'matamko'),
        'archive'       => esc_html__('Archive', 'matamko'),
        'search'        => esc_html__('Search', 'matamko'),
        '404'           => esc_html__('404', 'matamko'),
    ];

    if (class_exists('WooCommerce')) {
        $locations += [
            'woocommerce_shop'             => esc_html__('WooCommerce Shop', 'matamko'),
            'woocommerce_product'          => esc_html__('WooCommerce Product', 'matamko'),
            'woocommerce_product_category' => esc_html__('WooCommerce Product Category', 'matamko'),
            'woocommerce_product_tag'      => esc_html__('WooCommerce Product Tag', 'matamko'),
            'woocommerce_cart'             => esc_html__('WooCommerce Cart', 'matamko'),
            'woocommerce_checkout'         => esc_html__('WooCommerce Checkout', 'matamko'),
            'woocommerce_account'          => esc_html__('WooCommerce Account', 'matamko'),
            'woocommerce_thankyou'         => esc_html__('WooCommerce Thank You', 'matamko'),
        ];
    }

    return $locations;
}

function matamko_render_layout_metabox(WP_Post $post): void
{
    wp_nonce_field('matamko_save_layout', 'matamko_layout_nonce');

    $location_type = (string) get_post_meta($post->ID, '_matamko_location_type', true);
    $target_type   = (string) get_post_meta($post->ID, '_matamko_target_type', true);
    $object_ids    = (string) get_post_meta($post->ID, '_matamko_object_ids', true);
    $priority      = (int) get_post_meta($post->ID, '_matamko_priority', true);
    $is_active     = get_post_meta($post->ID, '_matamko_is_active', true) === '1';
    ?>
    <p>
        <label for="matamko_location_type"><strong><?php esc_html_e('Location Type', 'matamko'); ?></strong></label>
        <select id="matamko_location_type" name="matamko_location_type" class="widefat">
            <?php foreach (matamko_layout_location_types() as $value => $label) : ?>
                <option value="<?php echo esc_attr((string) $value); ?>" <?php selected($location_type, (string) $value); ?>>
                    <?php echo esc_html((string) $label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="matamko_target_type"><strong><?php esc_html_e('Target Type', 'matamko'); ?></strong></label>
        <input type="text" id="matamko_target_type" name="matamko_target_type" class="widefat" value="<?php echo esc_attr($target_type); ?>" placeholder="<?php esc_attr_e('post, product, taxonomy name, or wildcard', 'matamko'); ?>">
    </p>
    <p>
        <label for="matamko_object_ids"><strong><?php esc_html_e('Object IDs', 'matamko'); ?></strong></label>
        <input type="text" id="matamko_object_ids" name="matamko_object_ids" class="widefat" value="<?php echo esc_attr($object_ids); ?>" placeholder="<?php esc_attr_e('Comma-separated IDs, post type, term IDs, or *', 'matamko'); ?>">
    </p>
    <p>
        <label for="matamko_priority"><strong><?php esc_html_e('Priority', 'matamko'); ?></strong></label>
        <input type="number" id="matamko_priority" name="matamko_priority" class="widefat" value="<?php echo esc_attr((string) $priority); ?>">
    </p>
    <p>
        <label>
            <input type="checkbox" name="matamko_is_active" value="1" <?php checked($is_active); ?>>
            <?php esc_html_e('Set as active', 'matamko'); ?>
        </label>
    </p>
    <?php
}

function matamko_save_layout_post(int $post_id): void
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id) || get_post_type($post_id) !== 'theme_layout') {
        return;
    }

    if (! isset($_POST['matamko_layout_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['matamko_layout_nonce'])), 'matamko_save_layout')) {
        return;
    }

    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    $location_type = isset($_POST['matamko_location_type']) ? sanitize_key(wp_unslash($_POST['matamko_location_type'])) : '';
    $target_type   = isset($_POST['matamko_target_type']) ? sanitize_text_field(wp_unslash($_POST['matamko_target_type'])) : '';
    $object_ids    = isset($_POST['matamko_object_ids']) ? sanitize_text_field(wp_unslash($_POST['matamko_object_ids'])) : '';
    $priority      = isset($_POST['matamko_priority']) ? (string) intval(wp_unslash($_POST['matamko_priority'])) : '0';
    $is_active     = isset($_POST['matamko_is_active']) && sanitize_text_field(wp_unslash($_POST['matamko_is_active'])) === '1';

    if (! array_key_exists($location_type, matamko_layout_location_types())) {
        $location_type = 'home';
    }

    update_post_meta($post_id, '_matamko_location_type', $location_type);
    update_post_meta($post_id, '_matamko_target_type', $target_type);
    update_post_meta($post_id, '_matamko_object_ids', $object_ids);
    update_post_meta($post_id, '_matamko_priority', $priority);
    update_post_meta($post_id, '_matamko_is_active', $is_active ? '1' : '0');

    if ($is_active) {
        matamko_deactivate_duplicate_layouts($post_id, $location_type, $object_ids);
    }
}

function matamko_deactivate_duplicate_layouts(int $post_id, string $location_type, string $object_ids): void
{
    $duplicates = get_posts(
        [
            'post_type'      => 'theme_layout',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'post__not_in'   => [$post_id],
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'   => '_matamko_is_active',
                    'value' => '1',
                ],
                [
                    'key'   => '_matamko_location_type',
                    'value' => $location_type,
                ],
                [
                    'key'   => '_matamko_object_ids',
                    'value' => $object_ids,
                ],
            ],
        ],
    );

    foreach ($duplicates as $duplicate_id) {
        update_post_meta((int) $duplicate_id, '_matamko_is_active', '0');
    }
}

function matamko_layout_columns(array $columns): array
{
    $columns['matamko_location'] = esc_html__('Location', 'matamko');
    $columns['matamko_active']   = esc_html__('Status', 'matamko');

    return $columns;
}

function matamko_layout_column_content(string $column, int $post_id): void
{
    if ($column === 'matamko_active') {
        matamko_render_active_state_column($post_id);
        return;
    }

    if ($column === 'matamko_location') {
        echo esc_html((string) get_post_meta($post_id, '_matamko_location_type', true));
    }
}

function matamko_get_current_location(): array
{
    if (class_exists('WooCommerce')) {
        if (function_exists('is_shop') && is_shop()) {
            return ['location_type' => 'woocommerce_shop', 'object_id' => (string) wc_get_page_id('shop')];
        }

        if (function_exists('is_product') && is_product()) {
            return ['location_type' => 'woocommerce_product', 'object_id' => (string) get_queried_object_id()];
        }

        if (function_exists('is_product_category') && is_product_category()) {
            return ['location_type' => 'woocommerce_product_category', 'object_id' => (string) get_queried_object_id()];
        }

        if (function_exists('is_product_tag') && is_product_tag()) {
            return ['location_type' => 'woocommerce_product_tag', 'object_id' => (string) get_queried_object_id()];
        }

        if (function_exists('is_cart') && is_cart()) {
            return ['location_type' => 'woocommerce_cart', 'object_id' => (string) get_queried_object_id()];
        }

        if (function_exists('is_checkout') && is_checkout()) {
            $is_thankyou = function_exists('is_order_received_page') && is_order_received_page();

            return ['location_type' => $is_thankyou ? 'woocommerce_thankyou' : 'woocommerce_checkout', 'object_id' => (string) get_queried_object_id()];
        }

        if (function_exists('is_account_page') && is_account_page()) {
            return ['location_type' => 'woocommerce_account', 'object_id' => (string) get_queried_object_id()];
        }
    }

    if (is_front_page()) {
        return ['location_type' => 'home', 'object_id' => (string) get_queried_object_id()];
    }

    if (is_home()) {
        return ['location_type' => 'blog', 'object_id' => (string) get_option('page_for_posts')];
    }

    if (is_singular('post')) {
        return ['location_type' => 'single_post', 'object_id' => (string) get_queried_object_id()];
    }

    if (is_singular()) {
        return ['location_type' => 'single_cpt', 'object_id' => (string) get_post_type()];
    }

    if (is_category()) {
        return ['location_type' => 'category', 'object_id' => (string) get_queried_object_id()];
    }

    if (is_tax()) {
        return ['location_type' => 'taxonomy', 'object_id' => (string) get_queried_object_id()];
    }

    if (is_search()) {
        return ['location_type' => 'search', 'object_id' => ''];
    }

    if (is_404()) {
        return ['location_type' => '404', 'object_id' => ''];
    }

    if (is_archive()) {
        return ['location_type' => 'archive', 'object_id' => (string) get_queried_object_id()];
    }

    return ['location_type' => 'archive', 'object_id' => ''];
}

function matamko_find_matching_layout_id(): int
{
    $current = matamko_get_current_location();
    $layouts = get_posts(
        [
            'post_type'      => 'theme_layout',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_key'       => '_matamko_is_active',
            'meta_value'     => '1',
        ],
    );

    $matches = [];

    foreach ($layouts as $layout) {
        $location_type = (string) get_post_meta($layout->ID, '_matamko_location_type', true);

        if ($location_type !== $current['location_type']) {
            continue;
        }

        $object_ids  = matamko_parse_layout_object_ids((string) get_post_meta($layout->ID, '_matamko_object_ids', true));
        $is_exact    = in_array((string) $current['object_id'], $object_ids, true);
        $is_wildcard = in_array('*', $object_ids, true) || $object_ids === [];

        if (! $is_exact && ! $is_wildcard) {
            continue;
        }

        $matches[] = [
            'id'       => (int) $layout->ID,
            'exact'    => $is_exact ? 1 : 0,
            'priority' => (int) get_post_meta($layout->ID, '_matamko_priority', true),
            'date'     => strtotime((string) $layout->post_date_gmt) ?: 0,
        ];
    }

    if ($matches === []) {
        return 0;
    }

    usort(
        $matches,
        static function (array $a, array $b): int {
            return [$b['exact'], $b['priority'], $b['date'], $b['id']] <=> [$a['exact'], $a['priority'], $a['date'], $a['id']];
        },
    );

    return (int) $matches[0]['id'];
}

function matamko_parse_layout_object_ids(string $object_ids): array
{
    $items = array_map('trim', explode(',', $object_ids));
    $items = array_filter($items, static fn(string $item): bool => $item !== '');

    return array_values(array_map('sanitize_text_field', $items));
}

function matamko_layout_override(string $template): string
{
    if (is_admin() || matamko_is_elementor_editing_builder_post('theme_layout')) {
        return $template;
    }

    $layout_id = matamko_find_matching_layout_id();

    if ($layout_id <= 0) {
        return $template;
    }

    $layout_template = locate_template('template-parts/layout-builder.php');

    if ($layout_template === '') {
        return $template;
    }

    set_query_var('matamko_layout_id', $layout_id);

    return $layout_template;
}

function matamko_render_active_layout(): void
{
    $layout_id = (int) get_query_var('matamko_layout_id');

    if ($layout_id <= 0) {
        return;
    }

    $content = matamko_render_elementor_content($layout_id);

    if ($content === '') {
        return;
    }

    echo '<main id="primary" class="site-main site-main--elementor">';
    echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo '</main>';
}
