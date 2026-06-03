<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="primary" class="site-main">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : ?>
            <?php the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('entry'); ?>>
                <h1 class="entry-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h1>
                <div class="entry-content">
                    <?php the_excerpt(); ?>
                </div>
            </article>
        <?php endwhile; ?>
        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <p><?php esc_html_e('No content found.', 'matamko'); ?></p>
    <?php endif; ?>
</main>
<?php
get_footer();
