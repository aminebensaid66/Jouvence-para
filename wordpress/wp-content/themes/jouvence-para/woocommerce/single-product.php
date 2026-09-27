<?php
/**
 * Single product page shell.
 */

defined('ABSPATH') || exit;
get_header('shop');
?>
<main id="main-content" class="site-main jp-container jp-product-page" tabindex="-1">
    <?php woocommerce_breadcrumb(); ?>
    <?php while (have_posts()) : the_post(); ?>
        <?php wc_get_template_part('content', 'single-product'); ?>
    <?php endwhile; ?>
</main>
<?php
get_footer('shop');
