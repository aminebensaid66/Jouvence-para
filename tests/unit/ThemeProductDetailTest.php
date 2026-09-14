<?php

declare(strict_types=1);

return [
    'single product delegates commerce behavior to WooCommerce hooks' => static function (TestHarness $test): void {
        $source = (string) file_get_contents(__DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/woocommerce/content-single-product.php');
        foreach (['woocommerce_before_single_product_summary', 'woocommerce_single_product_summary', 'woocommerce_after_single_product_summary'] as $hook) {
            $test->assertTrue(str_contains($source, $hook));
        }
        $test->assertTrue(! str_contains($source, 'get_stock_quantity'));
    },
    'product page exposes named semantic regions' => static function (TestHarness $test): void {
        $source = (string) file_get_contents(__DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/woocommerce/content-single-product.php');
        $test->assertTrue(str_contains($source, '<article'));
        $test->assertTrue(str_contains($source, 'aria-label='));
    },
];
