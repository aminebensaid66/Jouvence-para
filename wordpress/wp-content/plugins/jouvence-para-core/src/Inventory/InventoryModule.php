<?php

declare(strict_types=1);

namespace JouvencePara\Core\Inventory;

use JouvencePara\Core\Contracts\Module;

final class InventoryModule implements Module
{
    private const SETTINGS_VERSION = '1';
    private const SETTINGS_OPTION = 'jp_inventory_settings_version';

    public function register(): void
    {
        add_action('admin_init', [$this, 'configureWooCommerce']);
        add_action('woocommerce_admin_process_product_object', [$this, 'enforceProductPolicy'], 40);
        add_filter('woocommerce_product_get_backorders', [$this, 'disableBackorders']);
        add_filter('woocommerce_product_variation_get_backorders', [$this, 'disableBackorders']);
        add_filter('woocommerce_product_is_in_stock', [$this, 'onlineInStock'], 20, 2);
        add_filter('woocommerce_variation_is_in_stock', [$this, 'onlineInStock'], 20, 2);
        add_filter('woocommerce_get_availability_text', [$this, 'availabilityText'], 20, 2);
        add_filter('woocommerce_add_to_cart_validation', [$this, 'validateAddToCart'], 20, 5);
    }

    public function configureWooCommerce(): void
    {
        if (! current_user_can('manage_options') || get_option(self::SETTINGS_OPTION, '') === self::SETTINGS_VERSION) {
            return;
        }
        $settings = [
            'woocommerce_manage_stock' => 'yes',
            'woocommerce_hold_stock_minutes' => (string) InventoryPolicy::RESERVATION_MINUTES,
            'woocommerce_notify_low_stock_amount' => (string) InventoryPolicy::LOW_STOCK_THRESHOLD,
            'woocommerce_hide_out_of_stock_items' => 'no',
        ];
        foreach ($settings as $key => $value) {
            if (! update_option($key, $value, false) && get_option($key, null) !== $value) {
                do_action('jouvence_para_inventory_configuration_error', $key);
                return;
            }
        }
        update_option(self::SETTINGS_OPTION, self::SETTINGS_VERSION, false);
    }

    public function enforceProductPolicy(object $product): void
    {
        if (method_exists($product, 'set_backorders')) {
            $product->set_backorders('no');
        }
        if (method_exists($product, 'set_manage_stock')) {
            $product->set_manage_stock(true);
        }
    }

    public function disableBackorders(mixed $value): string
    {
        unset($value);
        return 'no';
    }

    public function onlineInStock(bool $inStock, object $product): bool
    {
        if (! $inStock || ! method_exists($product, 'get_stock_quantity')) {
            return false;
        }
        return InventoryPolicy::onlineAvailable($product->get_stock_quantity()) > 0;
    }

    public function availabilityText(string $text, object $product): string
    {
        unset($text);
        $stock = method_exists($product, 'get_stock_quantity') ? $product->get_stock_quantity() : null;
        return InventoryPolicy::publicAvailability($stock) === 'available'
            ? __('Disponible', 'jouvence-para-core')
            : __('Rupture de stock', 'jouvence-para-core');
    }

    public function validateAddToCart(bool $passed, int $productId, int $quantity, int $variationId = 0, array $variations = []): bool
    {
        unset($variations);
        if (! $passed) {
            return false;
        }
        $product = wc_get_product($variationId > 0 ? $variationId : $productId);
        if (! $product || ! method_exists($product, 'get_stock_quantity')) {
            return false;
        }
        if (! InventoryPolicy::canPurchase($product->get_stock_quantity(), $quantity)) {
            wc_add_notice(__('La quantité demandée n’est pas disponible.', 'jouvence-para-core'), 'error');
            return false;
        }
        return true;
    }
}
