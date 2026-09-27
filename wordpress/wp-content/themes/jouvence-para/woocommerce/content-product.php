<?php
/**
 * Accessible product card.
 */

defined('ABSPATH') || exit;
global $product;
if (! $product instanceof WC_Product || ! $product->is_visible()) {
    return;
}
$productId = $product->get_id();
$permalink = get_permalink($productId);
$imageId = $product->get_image_id();
$brands = get_the_terms($productId, 'jp_brand');
$brand = is_array($brands) && isset($brands[0]) ? $brands[0]->name : '';
$available = $product->is_in_stock();
$quickAdd = $product->is_type('simple') && $product->is_purchasable() && $available && $product->get_price() !== '';
?>
<li <?php wc_product_class('jp-product-card', $product); ?>>
    <article class="jp-product-card__inner" aria-labelledby="jp-product-<?php echo esc_attr((string) $productId); ?>-title">
        <a class="jp-product-card__media" href="<?php echo esc_url($permalink); ?>" tabindex="-1" aria-hidden="true">
            <?php
            if ($imageId > 0) {
                echo wp_kses_post(wp_get_attachment_image($imageId, 'woocommerce_thumbnail', false, [
                    'class' => 'jp-product-card__image',
                    'loading' => 'lazy',
                    'decoding' => 'async',
                    'sizes' => '(min-width: 64rem) 25vw, (min-width: 40rem) 33vw, 50vw',
                ]));
            } else {
                echo wp_kses_post(wc_placeholder_img('woocommerce_thumbnail', ['class' => 'jp-product-card__image']));
            }
            ?>
        </a>
        <div class="jp-product-card__body">
            <?php if ($brand !== '') : ?><p class="jp-product-card__brand"><?php echo esc_html($brand); ?></p><?php endif; ?>
            <h2 class="jp-product-card__title" id="jp-product-<?php echo esc_attr((string) $productId); ?>-title">
                <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($product->get_name()); ?></a>
            </h2>
            <div class="jp-product-card__price"><?php echo wp_kses_post($product->get_price_html()); ?></div>
            <p class="jp-product-card__availability" data-status="<?php echo esc_attr($available ? 'available' : 'out-of-stock'); ?>">
                <?php echo esc_html($available ? __('Disponible', 'jouvence-para') : __('Rupture de stock', 'jouvence-para')); ?>
            </p>
            <?php if ($quickAdd) : ?>
                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>"
                   data-quantity="1"
                   data-product_id="<?php echo esc_attr((string) $productId); ?>"
                   class="jp-button add_to_cart_button ajax_add_to_cart"
                   rel="nofollow"
                   aria-label="<?php echo esc_attr(sprintf(__('Ajouter %s au panier', 'jouvence-para'), $product->get_name())); ?>">
                    <?php esc_html_e('Ajouter au panier', 'jouvence-para'); ?>
                </a>
            <?php else : ?>
                <a class="jp-button jp-button--secondary" href="<?php echo esc_url($permalink); ?>"><?php esc_html_e('Voir le produit', 'jouvence-para'); ?></a>
            <?php endif; ?>
        </div>
    </article>
</li>
