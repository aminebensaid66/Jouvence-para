<?php

declare(strict_types=1);

if (! function_exists('absint')) { function absint(mixed $value): int { return abs((int) $value); } }
if (! function_exists('taxonomy_exists')) { function taxonomy_exists(string $taxonomy): bool { return $taxonomy === 'jp_brand'; } }
if (! function_exists('get_terms')) {
    function get_terms(array $args): array|WP_Error
    {
        $terms = $GLOBALS['jp_test_promotion_terms'] ?? [];
        if (isset($args['include'])) {
            return array_values(array_filter($terms, static fn (object $term): bool => in_array((int) $term->term_id, $args['include'], true)));
        }
        return $terms;
    }
}
if (! function_exists('is_wp_error')) { function is_wp_error(mixed $thing): bool { return $thing instanceof WP_Error; } }
if (! function_exists('has_term')) {
    function has_term(array $terms, string $taxonomy, int $objectId): bool
    {
        unset($taxonomy);
        return (bool) array_intersect($terms, $GLOBALS['jp_test_product_brands'][$objectId] ?? []);
    }
}
if (! function_exists('add_action')) {
    function add_action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $GLOBALS['jp_test_hooks'][$hook] = [$callback, $priority, $acceptedArgs];
    }
}
if (! function_exists('add_filter')) {
    function add_filter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $GLOBALS['jp_test_hooks'][$hook] = [$callback, $priority, $acceptedArgs];
    }
}

if (! class_exists('WC_Coupon')) {
    class WC_Coupon
    {
        public const E_WC_COUPON_INVALID_FILTERED = 100;
        private array $meta = [];
        private bool $excludeSaleItems = false;
        private string $discountType;
        public function __construct(array $meta = [], bool $excludeSaleItems = false, string $discountType = 'percent') { $this->meta = $meta; $this->excludeSaleItems = $excludeSaleItems; $this->discountType = $discountType; }
        public function get_meta(string $key, bool $single = true): mixed { unset($single); return $this->meta[$key] ?? ''; }
        public function update_meta_data(string $key, mixed $value): void { $this->meta[$key] = $value; }
        public function delete_meta_data(string $key): void { unset($this->meta[$key]); }
        public function get_discount_type(): string { return $this->discountType; }
        public function get_exclude_sale_items(): bool { return $this->excludeSaleItems; }
        public function set_exclude_sale_items(bool $value): void { $this->excludeSaleItems = $value; }
        public function save(): void {}
    }
}
if (! class_exists('WC_Product')) {
    class WC_Product
    {
        public function __construct(private int $id, private int $parentId = 0) {}
        public function get_id(): int { return $this->id; }
        public function get_parent_id(): int { return $this->parentId; }
    }
}
if (! function_exists('current_user_can')) { function current_user_can(string $capability, int $objectId): bool { return $GLOBALS['jp_test_can_edit'] ?? false; } }
if (! function_exists('wp_unslash')) { function wp_unslash(mixed $value): mixed { return $value; } }
if (! function_exists('sanitize_text_field')) { function sanitize_text_field(string $value): string { return trim(strip_tags($value)); } }
if (! function_exists('wp_verify_nonce')) { function wp_verify_nonce(string $nonce, string $action): bool { return $nonce === 'valid' && $action === 'jp_save_coupon_promotion'; } }

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Promotions/PromotionSchedule.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Contracts/Module.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Promotions/PromotionModule.php';

use JouvencePara\Core\Promotions\PromotionModule;

return [
    'registers WooCommerce coupon schedule and brand enforcement filters' => static function (TestHarness $test): void {
        $before = count($GLOBALS['jp_test_hooks']['filter']['woocommerce_coupon_is_valid'] ?? []);
        $module = new PromotionModule();
        $module->register();
        $test->assertSame($before + 1, count($GLOBALS['jp_test_hooks']['filter']['woocommerce_coupon_is_valid']));
        $test->assertSame(3, $GLOBALS['jp_test_hooks']['filter']['woocommerce_coupon_is_valid'][$before][2]);
        $discountHooks = $GLOBALS['jp_test_hooks']['filter']['woocommerce_coupon_get_discount_amount'] ?? [];
        $test->assertSame(5, $discountHooks[count($discountHooks) - 1][2]);
    },
    'rejects a future coupon start and allows the exact start instant' => static function (TestHarness $test): void {
        $module = new PromotionModule();
        $start = time() + 60;
        $coupon = new WC_Coupon(['_jp_promotion_start_utc' => $start]);
        $test->assertTrue(! $module->couponHasStarted(true, $coupon));
        $test->assertSame('This promotion is not available yet.', $module->couponError('Coupon is not valid.', WC_Coupon::E_WC_COUPON_INVALID_FILTERED, $coupon));
        $test->assertSame('Unrelated coupon failure.', $module->couponError('Unrelated coupon failure.', WC_Coupon::E_WC_COUPON_INVALID_FILTERED, null));
        $freeShippingCoupon = new WC_Coupon([], false, 'free_shipping');
        $test->assertTrue(! $module->couponHasStarted(true, $freeShippingCoupon));
        $test->assertSame(
            'Free delivery is applied automatically when the cart reaches the approved threshold.',
            $module->couponError('Coupon is not valid.', WC_Coupon::E_WC_COUPON_INVALID_FILTERED, $freeShippingCoupon)
        );
        $coupon = new WC_Coupon(['_jp_promotion_start_utc' => time() - 1]);
        $test->assertTrue($module->couponHasStarted(true, $coupon));
    },
    'brand coupons only discount eligible products including variations' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_promotion_terms'] = [(object) ['term_id' => 8]];
        $GLOBALS['jp_test_brand_products'] = [100 => true];
        $module = new PromotionModule();
        $coupon = new WC_Coupon(['_jp_promotion_brand_ids' => [8]]);
        $test->assertSame(0.0, $module->brandDiscount(5.0, 50.0, ['data' => new WC_Product(['id' => 200])], false, $coupon));
        $test->assertSame(5.0, $module->brandDiscount(5.0, 50.0, ['data' => new WC_Product(['id' => 100])], false, $coupon));
        $saleProduct = new class(['id' => 100]) extends WC_Product {
            public function is_on_sale(): bool { return true; }
        };
        $test->assertSame(0.0, $module->brandDiscount(5.0, 50.0, ['data' => $saleProduct], false, new WC_Coupon()));
        $variation = new class(['id' => 200]) extends WC_Product {
            public function get_parent_id(): int { return 100; }
        };
        $test->assertSame(5.0, $module->brandDiscount(5.0, 50.0, ['data' => $variation], false, $coupon));

        $GLOBALS['jp_test_promotion_terms'] = [];
        $test->assertTrue(! $module->couponHasStarted(true, new WC_Coupon(['_jp_promotion_brand_ids' => [999]])));
        unset($GLOBALS['jp_test_promotion_terms'], $GLOBALS['jp_test_brand_products']);
    },
    'coupon metadata writes require both editor capability and a valid request nonce' => static function (TestHarness $test): void {
        $module = new PromotionModule();
        $coupon = new WC_Coupon(['_jp_promotion_start_utc' => 123]);
        $GLOBALS['jp_test_capability'] = false;
        $_POST = [
            'jp_coupon_promotion_nonce' => 'valid',
            'jp_promotion_start' => '2026-10-02T12:30',
        ];
        $module->saveFields(12, $coupon);
        $test->assertSame(123, $coupon->get_meta('_jp_promotion_start_utc'));

        $GLOBALS['jp_test_capability'] = true;
        $_POST['jp_coupon_promotion_nonce'] = 'invalid';
        $module->saveFields(12, $coupon);
        $test->assertSame(123, $coupon->get_meta('_jp_promotion_start_utc'));
        unset($_POST, $GLOBALS['jp_test_capability']);
    },
    'authorized coupon edits save Tunisian schedule as UTC and enforce sale exclusion' => static function (TestHarness $test): void {
        $module = new PromotionModule();
        $coupon = new WC_Coupon();
        $GLOBALS['jp_test_capability'] = true;
        $GLOBALS['jp_test_nonce_valid'] = true;
        $GLOBALS['jp_test_promotion_terms'] = [];
        $_POST = [
            'jp_coupon_promotion_nonce' => 'valid',
            'jp_promotion_start' => '2026-10-02T12:30',
        ];
        $module->saveFields(12, $coupon);
        $test->assertSame(
            \JouvencePara\Core\Promotions\PromotionSchedule::startTimestamp('2026-10-02T12:30'),
            $coupon->get_meta('_jp_promotion_start_utc')
        );
        $test->assertTrue($coupon->get_exclude_sale_items());
        unset($_POST, $GLOBALS['jp_test_capability'], $GLOBALS['jp_test_promotion_terms']);
    },
];
