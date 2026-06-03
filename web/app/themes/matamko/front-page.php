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
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    <?php endif; ?>
</main>
<?php
get_footer();
