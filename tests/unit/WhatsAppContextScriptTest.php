<?php

declare(strict_types=1);

return [
    'WhatsApp analytics payload excludes message content and requires consent' => static function (TestHarness $test): void {
        $source = (string) file_get_contents(__DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/assets/js/whatsapp-context.js');
        $test->assertTrue(str_contains($source, "allows?.('analytics')"));
        $test->assertTrue(str_contains($source, "emit?.('whatsapp_click', payload)"));
        $test->assertTrue(! str_contains($source, "new CustomEvent('jouvencepara:analytics'"));
        $test->assertTrue(! str_contains($source, 'message'));
        $test->assertTrue(! str_contains($source, 'document.cookie'));
    },
    'cart sharing is wired to classic and block cart views and omits unavailable products' => static function (TestHarness $test): void {
        $source = (string) file_get_contents(__DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/functions.php');
        $test->assertTrue(str_contains($source, "add_action('woocommerce_after_cart'"));
        $test->assertTrue(str_contains($source, "'woocommerce/cart'"));
        $test->assertTrue(str_contains($source, '$product->is_visible()'));
        $test->assertTrue(str_contains($source, "get_post_status((int) \$product->get_id()) !== 'publish'"));
    },
];
