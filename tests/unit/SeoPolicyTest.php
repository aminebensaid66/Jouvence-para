<?php

declare(strict_types=1);

use JouvencePara\Core\Discovery\SeoPolicy;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/SeoPolicy.php';

return [
    'builds readable descriptions from visible text and keeps UTF-8 intact' => static function (TestHarness $test): void {
        $test->assertSame('Soin & beauté pour peau sensible', SeoPolicy::description('<p>Soin &amp; beauté <strong>pour peau sensible</strong></p>'));
        $long = SeoPolicy::description('<p>' . str_repeat('crème solaire ', 20) . '</p>', 60);
        $test->assertSame(1, preg_match('//u', $long));
        $characters = preg_split('//u', $long, -1, PREG_SPLIT_NO_EMPTY);
        $test->assertTrue(is_array($characters) && count($characters) <= 60);
        $test->assertSame('…', SeoPolicy::description('long description', 1));
        $test->assertTrue(str_ends_with($long, '…'));
        $test->assertSame('', SeoPolicy::description('<script>do not expose</script><!-- secret --><style>display:none</style>'));
        $test->assertSame('', SeoPolicy::description('description', 0));
    },
    'sets French product and category rewrite bases without changing unrelated routes' => static function (TestHarness $test): void {
        $product = SeoPolicy::productRewrite(['rewrite' => ['feeds' => true]], 'product');
        $test->assertSame('produit', $product['rewrite']['slug']);
        $test->assertSame(false, $product['rewrite']['with_front']);
        $test->assertSame(true, $product['rewrite']['feeds']);
        $otherPostType = ['rewrite' => false];
        $test->assertSame($otherPostType, SeoPolicy::productRewrite($otherPostType, 'post'));
        $category = SeoPolicy::categoryRewrite(['rewrite' => ['hierarchical' => true]], 'product_cat');
        $test->assertSame('categorie', $category['rewrite']['slug']);
        $test->assertSame(true, $category['rewrite']['hierarchical']);
        $otherTaxonomy = ['rewrite' => false];
        $test->assertSame($otherTaxonomy, SeoPolicy::categoryRewrite($otherTaxonomy, 'jp_brand'));
    },
];
