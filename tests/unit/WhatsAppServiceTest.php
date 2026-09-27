<?php

declare(strict_types=1);

use JouvencePara\Core\Support\WhatsAppService;
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Support/WhatsAppService.php';

return [
    'uses the approved real WhatsApp number' => static function (TestHarness $test): void {
        $service = new WhatsAppService();
        $test->assertSame('https://wa.me/21629302202', $service->globalUrl());
    },
    'product link contains name and canonical URL but does not send automatically' => static function (TestHarness $test): void {
        $url = (new WhatsAppService())->productUrl('Crème douceur', 'https://example.test/produit/creme/');
        $decoded = rawurldecode($url);
        $test->assertTrue(str_contains($decoded, 'Crème douceur'));
        $test->assertTrue(str_contains($decoded, 'https://example.test/produit/creme/'));
        $test->assertTrue(str_starts_with($url, 'https://wa.me/21629302202?text='));
    },
];
