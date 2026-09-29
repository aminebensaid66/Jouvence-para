<?php

declare(strict_types=1);

namespace {
    class WC_Cache_Helper
    {
        public static function invalidate_cache_group(string $group): void { $GLOBALS['jp_cart_invalidated'][] = $group; }
    }
    class WC_Cart
    {
        public array $cart_contents = [];
        public int $calculations = 0;
        public string $cart_context = 'shortcode';
        public function get_cart(): array { return $this->cart_contents; }
        public function calculate_totals(): void
        {
            ++$this->calculations;
            $GLOBALS['jp_cart_wc']->session->data['cart'] = $this->cart_contents;
            $GLOBALS['jp_cart_wc']->session->data['cart_totals'] = 'recalculated';
        }
    }

    if (! class_exists('WP_Error')) {
        class WP_Error
        {
            public array $errors = [];
            public function __construct(public string $code = '', public string $message = '') {}
            public function add(string $code, string $message): void { $this->errors[$code] = $message; }
        }
    }

    function jp_test_validation_cart(int $quantity = 2): WC_Cart
    {
        $GLOBALS['jp_cart_notices'] = [];
        $GLOBALS['jp_cart_held'] = [];
        $GLOBALS['jp_cart_excluded'] = [];
        $GLOBALS['jp_cart_product_cache'] = [];
        $GLOBALS['jp_cart_instance_cache'] = [];
        $GLOBALS['jp_cart_instance_enabled'] = false;
        $GLOBALS['jp_cart_invalidated'] = [];
        $GLOBALS['jp_cart_products'] = [41 => jp_test_validation_product()];
        $cart = new WC_Cart();
        $cart->cart_contents['line'] = ['product_id' => 41, 'variation_id' => 0, 'quantity' => $quantity, 'data' => clone $GLOBALS['jp_cart_products'][41], 'options' => ['keep' => true], '_jp_catalog_price' => '32.900'];
        $GLOBALS['jp_cart_wc'] = (object) ['cart' => $cart, 'session' => new class {
            public array $data = [];
            public function get(string $key): mixed { return $this->data[$key] ?? null; }
        }];
        return $cart;
    }

    function jp_test_validation_product(array $data = []): WC_Product
    {
        return new class($data) extends WC_Product {
            public string $price = '32.900';
            public int $ownerId = 0;
            public ?int $parentStockSnapshot = null;
            public bool $purchasable = true;
            public bool $inStock = true;
            public bool $managed = true;
            public function get_price(): string { return $this->price; }
            public function set_price(string $price): void { $this->price = $price; }
            public function is_purchasable(): bool { return $this->purchasable; }
            public function is_in_stock(): bool { return $this->inStock && ($this->parentStockSnapshot === null || $this->parentStockSnapshot > 1); }
            public function managing_stock(): bool { return $this->managed; }
            public function get_stock_managed_by_id(): int { return $this->ownerId ?: $this->get_id(); }
        };
    }

    function jp_test_validation_order(int $id = 0): WC_Order
    {
        return new class($id) extends WC_Order {
            public ?array $rows = null;
            public function __construct(private int $id) {}
            public function get_id(): int { return $this->id; }
            public function get_items(): array
            {
                return array_map(static fn (array $row): object => new class($row) {
                    public function __construct(private array $row) {}
                    public function get_product_id(): int { return $this->row['product_id']; }
                    public function get_variation_id(): int { return $this->row['variation_id']; }
                    public function get_quantity(): mixed { return $this->row['quantity']; }
                }, $this->rows ?? $GLOBALS['jp_cart_wc']->cart->get_cart());
            }
        };
    }
}

namespace JouvencePara\Core\Cart {
    function WC(): object { return $GLOBALS['jp_cart_wc']; }
    function wc_get_product(int $id): \WC_Product|false
    {
        if ($GLOBALS['jp_cart_instance_enabled'] && isset($GLOBALS['jp_cart_instance_cache'][$id])) {
            return clone $GLOBALS['jp_cart_instance_cache'][$id];
        }
        if (! isset($GLOBALS['jp_cart_product_cache'][$id]) && isset($GLOBALS['jp_cart_products'][$id])) {
            $product = clone $GLOBALS['jp_cart_products'][$id];
            if ($product->ownerId > 0 && isset($GLOBALS['jp_cart_products'][$product->ownerId])) {
                $parent = wc_get_product($product->ownerId);
                $product->parentStockSnapshot = $parent->get_stock_quantity();
            }
            $GLOBALS['jp_cart_product_cache'][$id] = $product;
            if ($GLOBALS['jp_cart_instance_enabled']) { $GLOBALS['jp_cart_instance_cache'][$id] = clone $product; }
        }
        return isset($GLOBALS['jp_cart_product_cache'][$id]) ? clone $GLOBALS['jp_cart_product_cache'][$id] : false;
    }
    function wp_cache_delete(int $id, string $group): void { unset($GLOBALS['jp_cart_product_cache'][$id]); }
    function get_post_field(string $field, int $id): int { return $GLOBALS['jp_cart_products'][$id]->ownerId ?? 0; }
    function wc_get_container(): object
    {
        return new class {
            public function get(string $class): object { return new $class(); }
        };
    }
    function wc_format_decimal(string $price, int $decimals): string { return number_format((float) $price, $decimals, '.', ''); }
    function wc_get_price_decimals(): int { return 3; }
    function wc_get_held_stock_quantity(\WC_Product $product, int $excludeOrderId): int
    {
        $GLOBALS['jp_cart_excluded'][] = $excludeOrderId;
        return $GLOBALS['jp_cart_held'][$product->get_id()][$excludeOrderId] ?? 0;
    }
    function wc_has_notice(string $message, string $type): bool { return isset($GLOBALS['jp_cart_notices'][$type][$message]); }
    function wc_add_notice(string $message, string $type): void { $GLOBALS['jp_cart_notices'][$type][$message] = true; }
}

namespace Automattic\WooCommerce\StoreApi\Exceptions {
    class RouteException extends \Exception
    {
        public function __construct(public string $errorCode, string $message, int $status) { parent::__construct($message, $status); }
    }
}

namespace Automattic\WooCommerce\Utilities {
    class FeaturesUtil
    {
        public static function feature_is_enabled(string $name): bool { return $GLOBALS['jp_cart_instance_enabled']; }
    }
}

namespace Automattic\WooCommerce\Internal\Caches {
    class ProductCache
    {
        public function remove(int $id): void { unset($GLOBALS['jp_cart_instance_cache'][$id]); }
    }
}
