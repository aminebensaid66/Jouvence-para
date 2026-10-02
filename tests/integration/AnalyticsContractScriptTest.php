<?php

declare(strict_types=1);

return [
    'analytics script has the complete allow-list and consent purchase dedupe path' => static function (TestHarness $test): void {
        $source = (string) file_get_contents(__DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/assets/js/analytics-contract.js');
        foreach (['search_no_results', 'category_view', 'filter_use', 'product_view', 'add_to_cart', 'checkout_error', 'purchase', 'newsletter_signup', 'back_in_stock_request', 'account_registration', 'reorder'] as $event) {
            $test->assertTrue(str_contains($source, "'" . $event . "'"));
        }
        $test->assertTrue(str_contains($source, 'JouvenceParaConsent'));
        $test->assertTrue(str_contains($source, 'jouvencepara:consentchange'));
        $test->assertTrue(str_contains($source, 'jp_analytics_purchase_v1_'));
        $test->assertTrue(str_contains($source, 'sanitizePayload'));
        $test->assertTrue(str_contains($source, 'filter_use'));
    },
    'whatsapp analytics uses the shared contract emitter' => static function (TestHarness $test): void {
        $source = (string) file_get_contents(__DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/assets/js/whatsapp-context.js');
        $test->assertTrue(str_contains($source, 'JouvenceParaAnalytics?.emit'));
        $test->assertTrue(! str_contains($source, "new CustomEvent('jouvencepara:analytics'"));
    },
];
