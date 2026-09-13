<?php

get_header();
?>
<main id="main-content" class="site-main jp-container">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <p><?php esc_html_e('No content found.', 'jouvence-para'); ?></p>
    <?php endif; ?>
</main>
<?php
get_footer();
