<?php

declare(strict_types=1);

return [
    'product card does not expose exact stock quantity and uses semantic card markup' => static function (TestHarness $test): void {
        $path = __DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/woocommerce/content-product.php';
        $source = (string) file_get_contents($path);
        $test->assertTrue(str_contains($source, '<article'));
        $test->assertTrue(str_contains($source, 'aria-label='));
        $test->assertTrue(! str_contains($source, 'get_stock_quantity'));
        $test->assertTrue(str_contains($source, 'woocommerce_thumbnail'));
    },
    'archive grid has a named product collection' => static function (TestHarness $test): void {
        $source = (string) file_get_contents(__DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/woocommerce/archive-product.php');
        $test->assertTrue(str_contains($source, 'jp-product-grid'));
        $test->assertTrue(str_contains($source, 'aria-label='));
    },
];
