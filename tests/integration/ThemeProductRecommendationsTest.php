<?php

declare(strict_types=1);

require_once __DIR__ . '/theme-fixture.php';

return [
    'separates and stabilizes native WooCommerce recommendation lists' => static function (TestHarness $test): void {
        $filters = jp_test_theme_hooks()['filter'] ?? [];
        $callbackFor = static function (string $hook) use ($filters): callable {
            $registrations = $filters[$hook] ?? [];
            if ($registrations === []) {
                throw new RuntimeException('Missing theme filter: ' . $hook);
            }

            return $registrations[array_key_last($registrations)][0];
        };

        $test->assertSame(
            'Compléments de la routine',
            ($callbackFor('woocommerce_product_upsells_products_heading'))('You may also like')
        );
        $test->assertSame(
            'Produits similaires',
            ($callbackFor('woocommerce_product_related_products_heading'))('Related products')
        );
        $test->assertSame('menu_order', ($callbackFor('woocommerce_upsells_orderby'))('rand'));
        $test->assertSame('asc', ($callbackFor('woocommerce_upsells_order'))('desc'));
        $test->assertSame(false, ($callbackFor('woocommerce_product_related_posts_shuffle'))(true));

        $relatedArgs = ($callbackFor('woocommerce_output_related_products_args'))([
            'posts_per_page' => 4,
            'columns' => 4,
            'orderby' => 'rand',
            'order' => 'desc',
        ]);
        $test->assertSame('menu_order', $relatedArgs['orderby']);
        $test->assertSame('asc', $relatedArgs['order']);
        $test->assertSame(4, $relatedArgs['posts_per_page']);
    },
    'theme registrations survive unrelated global hook resets without overwriting them' => static function (TestHarness $test): void {
        $hooks = jp_test_theme_hooks();
        $GLOBALS['jp_test_hooks'] = ['unrelated' => ['sentinel']];

        $test->assertSame($hooks, jp_test_theme_hooks());
        $test->assertTrue(isset(jp_test_theme_hooks()['filter']['woocommerce_add_to_cart_fragments']));
        $test->assertSame(['unrelated' => ['sentinel']], $GLOBALS['jp_test_hooks']);
    },
    'product presentation keeps WooCommerce as the product-schema owner' => static function (TestHarness $test): void {
        $template = (string) file_get_contents(__DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/woocommerce/content-single-product.php');
        $themeFunctions = (string) file_get_contents(__DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/functions.php');

        $test->assertTrue(str_contains($template, 'woocommerce_single_product_summary'));
        $test->assertTrue(! str_contains($themeFunctions, 'application/ld+json'));
        $test->assertTrue(! str_contains($themeFunctions, 'woocommerce_structured_data_product'));
    },
];
