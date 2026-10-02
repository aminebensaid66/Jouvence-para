<?php

declare(strict_types=1);

namespace JouvencePara\Core\Analytics;

use JouvencePara\Core\Contracts\Module;
use JouvencePara\Core\Privacy\ConsentPreferences;
use WC_Order;

final class AnalyticsModule implements Module
{
    /** @var array<string, mixed>|null */
    private ?array $purchase = null;

    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue'], 30);
        add_action('woocommerce_thankyou', [$this, 'capturePurchase'], 20, 1);
        add_action('woocommerce_add_to_cart', [$this, 'captureAddToCart'], 20, 6);
        add_action('woocommerce_cart_item_removed', [$this, 'captureRemoveFromCart'], 20, 2);
        add_action('woocommerce_applied_coupon', [$this, 'captureCouponApplied'], 20, 1);
        add_filter('woocommerce_coupon_error', [$this, 'captureCouponRejected'], 20, 3);
        add_filter('woocommerce_registration_redirect', [$this, 'captureRegistration']);
        // WooCommerce renders the thank-you content before footer scripts print.
        // Emit configuration before the enqueued script executes in the footer.
        add_action('wp_print_footer_scripts', [$this, 'renderConfig'], 1);
    }

    public function enqueue(): void
    {
        if (! function_exists('wp_enqueue_script')) {
            return;
        }
        wp_enqueue_script(
            'jouvence-para-analytics',
            plugins_url('assets/js/analytics-contract.js', JOUVENCE_PARA_CORE_FILE),
            ['wp-data', 'jouvence-para-cookie-consent'],
            JOUVENCE_PARA_CORE_VERSION,
            true
        );
    }

    public function capturePurchase(int $orderId): void
    {
        $order = function_exists('wc_get_order') ? wc_get_order($orderId) : false;
        if (! $order instanceof WC_Order || ! $this->canViewOrder($order)
            || ! in_array($order->get_status(), ['processing', 'on-hold', 'completed'], true)) {
            return;
        }
        $items = [];
        foreach ($order->get_items('line_item') as $item) {
            $product = $item->get_product();
            $items[] = [
                'product_id' => $product ? (int) $product->get_id() : 0,
                'sku' => $product ? sanitize_text_field((string) $product->get_sku()) : '',
                'quantity' => (int) $item->get_quantity(),
                'price' => (int) $item->get_quantity() > 0 ? (float) $item->get_total() / (int) $item->get_quantity() : 0.0,
            ];
        }
        $purchaseId = hash('sha256', wp_salt('auth') . ':jp_purchase:' . $order->get_id());
        $this->purchase = AnalyticsEvent::make('purchase', [
            'purchase_id' => $purchaseId,
            'value' => (float) $order->get_total(),
            'currency' => sanitize_text_field((string) $order->get_currency()),
            'items' => $items,
            'coupon_count' => count($order->get_coupon_codes()),
        ]);
    }

    public function renderConfig(): void
    {
        $config = $this->context();
        $config['events'] = $this->drainPendingEvents();
        if ($this->purchase !== null) {
            $config['purchase'] = $this->purchase;
        }
        echo '<script>window.JouvenceParaAnalyticsConfig=' . wp_json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';</script>';
    }

    /** Capture only completed native classic requests; AJAX and Store API have client-side state events. */
    public function captureAddToCart(string $key, int $productId, int $quantity, int $variationId, array $variation, array $cartItemData): void
    {
        unset($key, $productId, $quantity, $variationId, $variation, $cartItemData);
        $this->queueEvent('add_to_cart');
    }

    public function captureRemoveFromCart(string $key, mixed $cart): void
    {
        unset($key, $cart);
        $this->queueEvent('remove_from_cart');
    }

    public function captureRegistration(string $redirect): string
    {
        $this->queueEvent('account_registration');
        return $redirect;
    }

    public function captureCouponApplied(string $couponCode): void
    {
        unset($couponCode);
        $this->queueEvent('coupon_applied');
    }

    public function captureCouponRejected(string $error, mixed $errorCode, mixed $coupon): string
    {
        unset($errorCode, $coupon);
        $this->queueEvent('coupon_rejected');
        return $error;
    }

    /** @return array<string, mixed> */
    private function context(): array
    {
        $context = [];
        if (function_exists('is_search') && is_search()) {
            $query = sanitize_text_field((string) get_search_query(false));
            if ($query !== '') {
                $context['search'] = [
                    'query_length' => (int) preg_match_all('/./us', $query),
                    'result_count' => isset($GLOBALS['wp_query']->found_posts) ? (int) $GLOBALS['wp_query']->found_posts : 0,
                ];
            }
        }
        if (function_exists('is_product') && is_product() && isset($GLOBALS['product']) && is_object($GLOBALS['product'])) {
            $product = $GLOBALS['product'];
            $context['product'] = [
                'product_id' => (int) $product->get_id(),
                'sku' => sanitize_text_field((string) $product->get_sku()),
                'price' => (float) $product->get_price(),
                'currency' => function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : 'TND',
            ];
            foreach (['jp_brand', 'product_cat'] as $taxonomy) {
                $terms = get_the_terms($product->get_id(), $taxonomy);
                if (is_array($terms) && isset($terms[0]->slug)) {
                    $context['product'][$taxonomy === 'jp_brand' ? 'brand' : 'category'] = sanitize_key((string) $terms[0]->slug);
                }
            }
        }
        if (function_exists('is_product_category') && (is_product_category() || (function_exists('is_shop') && is_shop()))) {
            $context['category'] = ['category' => sanitize_key((string) get_query_var('product_cat'))];
        }
        if (function_exists('is_cart') && is_cart()) {
            $context['cart'] = true;
        }
        if (function_exists('is_checkout') && is_checkout()
            && (! function_exists('is_order_received_page') || ! is_order_received_page())) {
            $context['checkout'] = true;
        }
        return $context;
    }

    private function canViewOrder(WC_Order $order): bool
    {
        $currentUserId = function_exists('get_current_user_id') ? (int) get_current_user_id() : 0;
        if ($currentUserId > 0 && (int) $order->get_customer_id() === $currentUserId) {
            return true;
        }
        $key = $_GET['key'] ?? null;
        if (! is_string($key) || strlen($key) > 128 || ! function_exists('wp_unslash')) {
            return false;
        }
        $key = wp_unslash($key);
        $orderKey = (string) $order->get_order_key();
        return is_string($key) && $orderKey !== '' && hash_equals($orderKey, $key);
    }

    private function queueEvent(string $event): void
    {
        if (! in_array($event, ['add_to_cart', 'remove_from_cart', 'coupon_applied', 'coupon_rejected', 'account_registration'], true)
            || ! ConsentPreferences::allows('analytics')
            || (function_exists('wp_doing_ajax') && wp_doing_ajax()) || (defined('REST_REQUEST') && REST_REQUEST)
            || ! function_exists('WC')) {
            return;
        }
        $session = WC()->session ?? null;
        if (! is_object($session) || ! is_callable([$session, 'get']) || ! is_callable([$session, 'set'])) {
            return;
        }
        $events = $session->get('jp_analytics_pending_events', []);
        $events = is_array($events) ? $events : [];
        $events[$event] = min(100, (int) ($events[$event] ?? 0) + 1);
        $session->set('jp_analytics_pending_events', $events);
    }

    /** @return array<string, int> */
    private function drainPendingEvents(): array
    {
        if (! function_exists('WC') || ! is_object(WC()->session ?? null)) {
            return [];
        }
        $session = WC()->session;
        if (! is_callable([$session, 'get']) || ! is_callable([$session, 'set'])) {
            return [];
        }
        $events = $session->get('jp_analytics_pending_events', []);
        $session->set('jp_analytics_pending_events', []);
        if (! is_array($events) || ! ConsentPreferences::allows('analytics')) {
            return [];
        }
        $allowed = ['add_to_cart', 'remove_from_cart', 'coupon_applied', 'coupon_rejected', 'account_registration'];
        $safe = [];
        foreach ($events as $name => $count) {
            if (in_array($name, $allowed, true) && is_numeric($count)) {
                $safe[$name] = min(100, max(0, (int) $count));
            }
        }
        return $safe;
    }
}
