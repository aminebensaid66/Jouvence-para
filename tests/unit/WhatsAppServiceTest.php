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
    'cart share includes only bounded public product context and confirms live price and stock' => static function (TestHarness $test): void {
        $items = [];
        for ($index = 1; $index <= 11; ++$index) {
            $items[] = ['name' => 'Crème ' . $index, 'url' => 'https://store.example/produit/' . $index . '/', 'quantity' => 2,
                'email' => 'private@example.test', 'phone' => '+21600000000', 'session' => 'session-secret'];
        }
        $url = (new WhatsAppService())->cartUrl($items);
        $message = rawurldecode((string) parse_url($url, PHP_URL_QUERY));
        $test->assertTrue(str_starts_with($url, 'https://wa.me/21629302202?text='));
        $test->assertTrue(str_contains($message, '2 × Crème 1 : https://store.example/produit/1/'));
        $test->assertTrue(str_contains($message, '1 autre(s) article(s) non inclus'));
        $test->assertTrue(str_contains($message, 'Prix et disponibilité à confirmer'));
        $test->assertTrue(! str_contains($message, 'private@example.test'));
        $test->assertTrue(! str_contains($message, '+21600000000'));
        $test->assertTrue(! str_contains($message, 'session-secret'));
        $test->assertTrue(! str_contains($message, 'Crème 11'));
    },
    'cart share rejects URLs with credentials tracking parameters fragments or foreign hosts' => static function (TestHarness $test): void {
        $items = [
            ['name' => 'Credentials', 'url' => 'https://user:pass@store.example/item/', 'quantity' => 1],
            ['name' => 'Tracking', 'url' => 'https://store.example/item/?session_token=secret', 'quantity' => 1],
            ['name' => 'Fragment', 'url' => 'https://store.example/item/#private', 'quantity' => 1],
        ];
        $test->assertSame('', (new WhatsAppService())->cartUrl($items));
    },
];
