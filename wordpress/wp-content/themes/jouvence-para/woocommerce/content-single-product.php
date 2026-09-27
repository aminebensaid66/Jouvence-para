<?php
/**
 * Single-product presentation. Commerce behavior stays in WooCommerce hooks.
 */

defined('ABSPATH') || exit;
global $product;
if (! $product instanceof WC_Product) {
    return;
}
?>
<?php do_action('woocommerce_before_single_product'); ?>
<article id="product-<?php the_ID(); ?>" <?php wc_product_class('jp-product-detail', $product); ?>>
    <div class="jp-product-detail__primary">
        <section class="jp-product-detail__gallery" aria-label="<?php esc_attr_e('Images du produit', 'jouvence-para'); ?>">
            <?php do_action('woocommerce_before_single_product_summary'); ?>
        </section>
        <section class="summary entry-summary jp-product-detail__summary" aria-label="<?php esc_attr_e('Informations et achat', 'jouvence-para'); ?>">
            <?php do_action('woocommerce_single_product_summary'); ?>
        </section>
    </div>
    <section class="jp-product-detail__details" aria-label="<?php esc_attr_e('Détails du produit', 'jouvence-para'); ?>">
        <?php do_action('woocommerce_after_single_product_summary'); ?>
    </section>
</article>
<?php do_action('woocommerce_after_single_product'); ?>
