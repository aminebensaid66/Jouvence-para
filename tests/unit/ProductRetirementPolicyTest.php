<?php

declare(strict_types=1);

use JouvencePara\Core\Discovery\ProductRetirementPolicy as Policy;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/ProductRetirementPolicy.php';

return [
    'normalizes known product routes while discarding tracking and filter noise' => static function (TestHarness $test): void {
        $test->assertSame('/produit/crème', Policy::route('https://store.example/produit/cr%C3%A8me/?utm_source=mail&orderby=price'));
        $test->assertSame(Policy::route('/produit/crème'), Policy::route('/produit/cr%c3%a8me/'));
        $test->assertSame('/?p=41&post_type=product', Policy::route('/?post_type=product&utm_source=mail&p=41'));
        $test->assertSame('', Policy::route('/?p[]=41'));
        $test->assertSame('', Policy::route('/'));
        $test->assertSame('', Policy::route('/produit/../autre'));
        $test->assertSame('', Policy::route('/produit/soin%0A'));
        $test->assertTrue(Policy::optionName('/produit/a') !== Policy::optionName('/produit/b'));
    },
    'replacement URLs reject external credentials ports downgrade and redirect loops' => static function (TestHarness $test): void {
        $home = 'https://store.example/';
        $test->assertSame('https://store.example/produit/b/', Policy::replacementUrl('https://store.example/produit/b/', $home, '/produit/a'));
        foreach (['https://other.example/produit/b/', '//store.example/produit/b/', 'javascript:alert(1)',
            'https://user:password@store.example/produit/b/', 'https://store.example:8443/produit/b/',
            'http://store.example/produit/b/', 'https://store.example/produit/a/?utm_source=mail'] as $url) {
            $test->assertSame('', Policy::replacementUrl($url, $home, '/produit/a'), $url);
        }
    },
];
