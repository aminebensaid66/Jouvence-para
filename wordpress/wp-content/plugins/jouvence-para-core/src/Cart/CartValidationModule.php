<?php

declare(strict_types=1);

namespace JouvencePara\Core\Cart;

use JouvencePara\Core\Contracts\Module;
use JouvencePara\Core\Inventory\InventoryPolicy;
use WC_Cart;
use WC_Order;
use WC_Product;
use WP_Error;

final class CartValidationModule implements Module
{
    private const PRICE = '_jp_catalog_price';
    private bool $priceChanged = false;
    private bool $refreshing = false;

    public function register(): void
    {
        add_filter('woocommerce_add_cart_item', [$this, 'rememberPrice'], 10, 2);
        add_action('woocommerce_cart_loaded_from_session', [$this, 'cartLoaded']);
        add_action('woocommerce_before_calculate_totals', [$this, 'refreshPrices'], 5);
        add_action('woocommerce_check_cart_items', [$this, 'checkCart']);
        add_action('woocommerce_store_api_cart_errors', [$this, 'storeCartErrors'], 10, 2);
        add_action('woocommerce_checkout_create_order', [$this, 'finalOrder'], PHP_INT_MAX);
        add_action('woocommerce_store_api_checkout_update_order_meta', [$this, 'finalStoreOrder'], PHP_INT_MAX);
        add_action('woocommerce_store_api_checkout_order_processed', [$this, 'finalStoreOrder'], PHP_INT_MAX);
    }

    public function rememberPrice(array $data, string $cartItemKey = ''): array
    {
        unset($cartItemKey);
        // Capture after native cart-key generation so a changed price cannot split identical products.
        $product = $this->freshProduct((int) (($data['variation_id'] ?? 0) ?: ($data['product_id'] ?? 0)));
        if ($product instanceof WC_Product) {
            $data[self::PRICE] = self::price($product);
        }
        return $data;
    }

    private static function price(WC_Product $product): string
    {
        $price = $product->get_price();
        return $price === '' ? '' : wc_format_decimal($price, wc_get_price_decimals());
    }

    private function freshProduct(int $id): WC_Product|false
    {
        if ($id < 1) {
            return false;
        }
        // Separate requests can change records already cached during this checkout request.
        $this->clearProductCache($id);
        $parentId = (int) get_post_field('post_parent', $id);
        if ($parentId > 0) {
            $this->clearProductCache($parentId);
        }
        return wc_get_product($id);
    }

    private function clearProductCache(int $id): void
    {
        wp_cache_delete($id, 'posts');
        wp_cache_delete($id, 'post_meta');
        wp_cache_delete($id, 'product_type_relationships');
        \WC_Cache_Helper::invalidate_cache_group('product_' . $id);
        // WooCommerce 10.5+ optionally caches whole instances, independently of WP metadata.
        $cacheClass = \Automattic\WooCommerce\Internal\Caches\ProductCache::class;
        if (class_exists($cacheClass) && \Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled('product_instance_caching')) {
            wc_get_container()->get($cacheClass)->remove($id);
        }
    }

    public function refreshPrices(WC_Cart $cart): bool
    {
        if ($this->refreshing) {
            return false;
        }
        $changed = false;
        $products = [];
        $this->refreshing = true;
        try {
            foreach ($cart->get_cart() as $key => $item) {
                $id = (int) (($item['variation_id'] ?? 0) ?: ($item['product_id'] ?? 0));
                if (! array_key_exists($id, $products)) {
                    $products[$id] = $this->freshProduct($id);
                }
                $product = $products[$id];
                if (! $product instanceof WC_Product) {
                    continue;
                }
                $price = self::price($product);
                $changed = $changed || ! isset($item[self::PRICE]) || $item[self::PRICE] !== $price;
                if (isset($item[self::PRICE]) && $item[self::PRICE] !== $price) {
                    $this->priceChanged = true;
                    $this->notice(__('Le prix d’un produit a changé. Vérifiez le total actualisé du panier avant de commander.', 'jouvence-para-core'), 'notice');
                }
                // Keep quantities, options and coupon data; native totals recalculate from current products.
                $cart->cart_contents[$key]['data'] = clone $product;
                $cart->cart_contents[$key][self::PRICE] = $price;
            }
        } finally {
            $this->refreshing = false;
        }
        return $changed;
    }

    public function cartLoaded(WC_Cart $cart): void
    {
        if ($this->refreshPrices($cart)) {
            // Native session loading can retain cached totals unless explicitly recalculated.
            $cart->calculate_totals();
        }
        foreach ($this->stockErrors($cart, $this->currentOrderId($cart)) as $message) {
            $this->notice($message, 'error');
        }
    }

    /** @return list<string> */
    private function stockErrors(WC_Cart $cart, int $excludeOrderId = 0, ?array $orderItems = null): array
    {
        $demand = [];
        $products = [];
        $loaded = [];
        $invalid = false;
        foreach ($orderItems ?? $cart->get_cart() as $item) {
            $id = (int) (($item['variation_id'] ?? 0) ?: ($item['product_id'] ?? 0));
            if (! array_key_exists($id, $loaded)) {
                $loaded[$id] = $this->freshProduct($id);
            }
            $product = $loaded[$id];
            $quantity = $item['quantity'] ?? 0;
            if (! $product instanceof WC_Product || ! is_numeric($quantity) || ! is_finite((float) $quantity) || (float) $quantity <= 0
                || floor((float) $quantity) !== (float) $quantity || ! $product->is_purchasable() || ! $product->is_in_stock()
                || ! $product->managing_stock()) {
                $invalid = true;
                continue;
            }
            $ownerId = $product->get_stock_managed_by_id();
            if (! array_key_exists($ownerId, $loaded)) {
                $loaded[$ownerId] = $this->freshProduct($ownerId);
            }
            $owner = $loaded[$ownerId];
            if (! $owner instanceof WC_Product) {
                $invalid = true;
                continue;
            }
            $products[$ownerId] = $owner;
            $demand[$ownerId] = ($demand[$ownerId] ?? 0) + (int) $quantity;
        }
        foreach ($demand as $id => $quantity) {
            $held = wc_get_held_stock_quantity($products[$id], $excludeOrderId);
            $available = max(0, InventoryPolicy::onlineAvailable($products[$id]->get_stock_quantity('edit')) - $held);
            if ($quantity > $available) {
                $invalid = true;
            }
        }
        return $invalid ? [__('Un produit est indisponible ou la quantité dépasse la disponibilité actuelle. Réduisez la quantité ou retirez ce produit du panier avant de commander.', 'jouvence-para-core')] : [];
    }

    private function currentOrderId(WC_Cart $cart): int
    {
        $session = WC()->session;
        $key = $cart->cart_context === 'store-api' ? 'store_api_draft_order' : 'order_awaiting_payment';
        return $session ? max(0, (int) $session->get($key)) : 0;
    }

    /** @return list<string> */
    private function checkoutErrors(WC_Cart $cart, int $orderId = 0): array
    {
        if ($this->refreshPrices($cart)) {
            $cart->calculate_totals();
        }
        $errors = $this->stockErrors($cart, $orderId);
        if ($this->priceChanged) {
            $errors[] = __('Les prix ont été actualisés. Vérifiez votre panier et envoyez à nouveau la commande.', 'jouvence-para-core');
        }
        return $errors;
    }

    public function checkCart(): void
    {
        $cart = WC()->cart;
        if ($cart instanceof WC_Cart) {
            foreach ($this->checkoutErrors($cart, $this->currentOrderId($cart)) as $message) {
                $this->notice($message, 'error');
            }
        }
    }

    public function storeCartErrors(WP_Error $errors, WC_Cart $cart): void
    {
        foreach ($this->checkoutErrors($cart, $this->currentOrderId($cart)) as $index => $message) {
            $errors->add('jp_cart_validation_' . $index, $message);
        }
    }

    public function finalOrder(WC_Order $order): void
    {
        $cart = WC()->cart;
        if (! $cart instanceof WC_Cart) {
            throw new \Exception(__('Votre panier n’a pas pu être vérifié. Rechargez la page et réessayez.', 'jouvence-para-core'));
        }
        $errors = $this->checkoutErrors($cart, $order->get_id());
        $items = [];
        foreach ($order->get_items() as $item) {
            $items[] = ['product_id' => $item->get_product_id(), 'variation_id' => $item->get_variation_id(), 'quantity' => $item->get_quantity()];
        }
        if ($items === [] && $cart->get_cart() !== []) {
            $errors[] = __('Les articles de votre commande n’ont pas pu être vérifiés. Rechargez votre panier.', 'jouvence-para-core');
        }
        $errors = array_unique(array_merge($errors, $this->stockErrors($cart, $order->get_id(), $items)));
        if ($errors !== []) {
            throw new \Exception(implode(' ', $errors));
        }
    }

    public function finalStoreOrder(WC_Order $order): void
    {
        try {
            $this->finalOrder($order);
        } catch (\Exception $exception) {
            throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException('jp_cart_changed', $exception->getMessage(), 409);
        }
    }

    private function notice(string $message, string $type): void
    {
        if (! wc_has_notice($message, $type)) {
            wc_add_notice($message, $type);
        }
    }
}
