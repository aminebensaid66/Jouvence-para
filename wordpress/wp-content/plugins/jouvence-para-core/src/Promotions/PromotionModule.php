<?php

declare(strict_types=1);

namespace JouvencePara\Core\Promotions;

use JouvencePara\Core\Contracts\Module;
use WC_Coupon;
use WC_Product;

final class PromotionModule implements Module
{
    private const START_META = '_jp_promotion_start_utc';
    private const BRANDS_META = '_jp_promotion_brand_ids';
    private const NONCE_ACTION = 'jp_save_coupon_promotion';
    private const NONCE_NAME = 'jp_coupon_promotion_nonce';

    public function register(): void
    {
        add_action('woocommerce_coupon_options_usage_restriction', [$this, 'renderFields'], 10, 2);
        add_action('woocommerce_coupon_options_save', [$this, 'saveFields'], 10, 2);
        add_filter('woocommerce_coupon_is_valid', [$this, 'couponHasStarted'], 10, 3);
        add_filter('woocommerce_coupon_error', [$this, 'couponError'], 10, 3);
        add_filter('woocommerce_coupon_get_discount_amount', [$this, 'brandDiscount'], 10, 5);
    }

    public function renderFields(int $couponId, WC_Coupon $coupon): void
    {
        unset($couponId);
        if (! taxonomy_exists('jp_brand')) {
            return;
        }

        $start = $coupon->get_meta(self::START_META, true);
        $localStart = '';
        if (is_numeric($start)) {
            $localStart = (new \DateTimeImmutable('@' . (int) $start))
                ->setTimezone(new \DateTimeZone(PromotionSchedule::TIMEZONE))
                ->format('Y-m-d\TH:i');
        }

        $selectedBrands = array_map('absint', (array) $coupon->get_meta(self::BRANDS_META, true));
        $brands = get_terms(['taxonomy' => 'jp_brand', 'hide_empty' => false]);

        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
        echo '<p class="form-field"><label for="jp_promotion_start">' . esc_html__('Promotion starts (Africa/Tunis)', 'jouvence-para-core') . '</label>';
        echo '<input type="datetime-local" id="jp_promotion_start" name="jp_promotion_start" value="' . esc_attr($localStart) . '" />';
        echo '<span class="description">' . esc_html__('Leave blank to make the coupon available immediately. WooCommerce controls the end date.', 'jouvence-para-core') . '</span></p>';
        echo '<p class="form-field"><label for="jp_promotion_brand_ids">' . esc_html__('Eligible brands', 'jouvence-para-core') . '</label>';
        echo '<select id="jp_promotion_brand_ids" name="jp_promotion_brand_ids[]" multiple="multiple" class="wc-enhanced-select" style="width: 50%;">';
        if (is_array($brands) && ! is_wp_error($brands)) {
            foreach ($brands as $brand) {
                echo '<option value="' . esc_attr((string) $brand->term_id) . '" ' . selected(in_array((int) $brand->term_id, $selectedBrands, true), true, false) . '>' . esc_html($brand->name) . '</option>';
            }
        }
        echo '</select><span class="description">' . esc_html__('When selected, this coupon discounts matching brand products only.', 'jouvence-para-core') . '</span></p>';
    }

    public function saveFields(int $couponId, WC_Coupon $coupon): void
    {
        if ($couponId < 1 || ! current_user_can('edit_post', $couponId)
            || ! isset($_POST[self::NONCE_NAME]) || ! is_string($_POST[self::NONCE_NAME])
            || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
            return;
        }

        $rawStart = isset($_POST['jp_promotion_start']) && is_string($_POST['jp_promotion_start'])
            ? sanitize_text_field(wp_unslash($_POST['jp_promotion_start']))
            : '';
        try {
            $start = PromotionSchedule::startTimestamp($rawStart);
        } catch (\InvalidArgumentException) {
            return;
        }

        if ($start === null) {
            $coupon->delete_meta_data(self::START_META);
        } else {
            $coupon->update_meta_data(self::START_META, $start);
        }

        if (taxonomy_exists('jp_brand')) {
            $brands = isset($_POST['jp_promotion_brand_ids']) && is_array($_POST['jp_promotion_brand_ids'])
                ? array_values(array_unique(array_filter(array_map('absint', wp_unslash($_POST['jp_promotion_brand_ids'])))))
                : [];
            $controlledBrands = $this->controlledBrandIds($brands);
            if (count($controlledBrands) !== count($brands)) {
                return;
            }
            if ($controlledBrands === []) {
                $coupon->delete_meta_data(self::BRANDS_META);
            } else {
                $coupon->update_meta_data(self::BRANDS_META, $controlledBrands);
            }
        }

        // The approved launch rule excludes sale-priced products from all coupons.
        if (! $coupon->get_exclude_sale_items()) {
            $coupon->set_exclude_sale_items(true);
        }
        $coupon->save();
    }

    public function couponHasStarted(bool $valid, WC_Coupon $coupon, mixed $discounts = null): bool
    {
        unset($discounts);
        if (! $valid || $coupon->get_discount_type() === 'free_shipping') {
            return false;
        }

        $start = $coupon->get_meta(self::START_META, true);
        if (is_numeric($start) && ! PromotionSchedule::hasStarted((int) $start, time())) {
            return false;
        }

        $savedBrands = $this->savedBrandIds($coupon);
        if ($savedBrands === []) {
            return true;
        }
        $brands = $this->controlledBrandIds($savedBrands);
        if ($brands === []) {
            return false;
        }

        $cart = function_exists('WC') ? WC()->cart : null;
        if (! $cart || ! method_exists($cart, 'get_cart')) {
            return false;
        }

        foreach ($cart->get_cart() as $item) {
            if (($item['data'] ?? null) instanceof WC_Product && $this->productHasBrand($item['data'], $brands)) {
                return true;
            }
        }

        return false;
    }

    public function couponError(string $message, int $errorCode, ?WC_Coupon $coupon): string
    {
        if ($coupon !== null && $errorCode === WC_Coupon::E_WC_COUPON_INVALID_FILTERED) {
            if ($coupon->get_discount_type() === 'free_shipping') {
                return __('Free delivery is applied automatically when the cart reaches the approved threshold.', 'jouvence-para-core');
            }

            $start = $coupon->get_meta(self::START_META, true);
            if (is_numeric($start) && ! PromotionSchedule::hasStarted((int) $start, time())) {
                return __('This promotion is not available yet.', 'jouvence-para-core');
            }

            if ($this->hasBrandRestriction($coupon) && ! $this->cartHasEligibleBrand($coupon)) {
                return __('This promotion is not available for the products in your cart.', 'jouvence-para-core');
            }
        }

        return $message;
    }

    /** Keep a brand coupon's discount limited to matching products in mixed carts. */
    public function brandDiscount(float $discount, float $discountingAmount, array $cartItem, bool $single, WC_Coupon $coupon): float
    {
        unset($discountingAmount, $single);
        $savedBrands = $this->savedBrandIds($coupon);
        $product = $cartItem['data'] ?? null;
        if ($product instanceof WC_Product && method_exists($product, 'is_on_sale') && $product->is_on_sale()) {
            return 0.0;
        }
        $brands = $this->controlledBrandIds($savedBrands);
        if ($savedBrands !== [] && ($brands === [] || ! $product instanceof WC_Product || ! $this->productHasBrand($product, $brands))) {
            return 0.0;
        }

        return $discount;
    }

    /** @param list<int> $brandIds */
    private function productHasBrand(WC_Product $product, array $brandIds): bool
    {
        if (! taxonomy_exists('jp_brand')) {
            return false;
        }

        $parentId = method_exists($product, 'get_parent_id') ? $product->get_parent_id() : 0;
        $ids = array_filter([$product->get_id(), $parentId]);
        foreach ($ids as $id) {
            foreach ($brandIds as $brandId) {
                if (has_term((string) $brandId, 'jp_brand', $id)) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @param list<int> $brandIds
     * @return list<int>
     */
    private function controlledBrandIds(array $brandIds): array
    {
        if (! taxonomy_exists('jp_brand')) {
            return [];
        }

        $terms = get_terms(['taxonomy' => 'jp_brand', 'hide_empty' => false, 'include' => $brandIds]);
        if (! is_array($terms) || is_wp_error($terms)) {
            return [];
        }

        $known = array_map(static fn (object $term): int => (int) $term->term_id, $terms);
        return array_values(array_intersect($brandIds, $known));
    }

    private function hasBrandRestriction(WC_Coupon $coupon): bool
    {
        return $this->savedBrandIds($coupon) !== [];
    }

    private function cartHasEligibleBrand(WC_Coupon $coupon): bool
    {
        $savedBrands = $this->savedBrandIds($coupon);
        $brands = $this->controlledBrandIds($savedBrands);
        $cart = function_exists('WC') ? WC()->cart : null;
        if ($savedBrands === [] || $brands === [] || ! $cart || ! method_exists($cart, 'get_cart')) {
            return false;
        }

        foreach ($cart->get_cart() as $item) {
            if (($item['data'] ?? null) instanceof WC_Product && $this->productHasBrand($item['data'], $brands)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<int> */
    private function savedBrandIds(WC_Coupon $coupon): array
    {
        $stored = $coupon->get_meta(self::BRANDS_META, true);
        if (! is_array($stored)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('absint', $stored))));
    }
}
