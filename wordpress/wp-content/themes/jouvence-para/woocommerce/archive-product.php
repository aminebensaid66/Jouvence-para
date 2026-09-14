<?php
/**
 * Product archive presentation for Jouvence Para.
 */

defined('ABSPATH') || exit;
get_header('shop');
?>
<main id="main-content" class="site-main jp-container jp-shop" tabindex="-1">
    <?php woocommerce_breadcrumb(); ?>
    <header class="jp-shop__header">
        <?php if (apply_filters('woocommerce_show_page_title', true)) : ?>
            <h1><?php woocommerce_page_title(); ?></h1>
        <?php endif; ?>
        <?php do_action('woocommerce_archive_description'); ?>
    </header>

    <?php if (woocommerce_product_loop()) : ?>
        <?php do_action('woocommerce_before_shop_loop'); ?>
        <ul class="products jp-product-grid" aria-label="<?php esc_attr_e('Produits', 'jouvence-para'); ?>">
            <?php while (have_posts()) : the_post(); ?>
                <?php wc_get_template_part('content', 'product'); ?>
            <?php endwhile; ?>
        </ul>
        <?php do_action('woocommerce_after_shop_loop'); ?>
    <?php else : ?>
        <?php do_action('woocommerce_no_products_found'); ?>
    <?php endif; ?>
</main>
<?php
get_footer('shop');
