<?php

declare(strict_types=1);

use JouvencePara\Core\Catalog\IdentifierLookup;
use JouvencePara\Core\Catalog\ProductData;
use JouvencePara\Core\Catalog\ProductDataValidator;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/IdentifierLookup.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/ProductData.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/ProductValidationResult.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/TndMoney.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/ProductDataValidator.php';

$lookup = static function (array $skus = [], array $eans = []): IdentifierLookup {
    return new class($skus, $eans) implements IdentifierLookup {
        /** @param array<string, int> $skus @param array<string, int> $eans */
        public function __construct(private array $skus, private array $eans)
        {
        }

        public function skuOwner(string $sku): int
        {
            return $this->skus[$sku] ?? 0;
        }

        public function eanOwner(string $ean, int $excludeProductId = 0): int
        {
            $owner = $this->eans[$ean] ?? 0;
            return $owner === $excludeProductId ? 0 : $owner;
        }
    };
};

$product = static function (array $overrides = []): ProductData {
    $data = [
        'id' => 41,
        'name' => 'Nettoyant doux 200 ml',
        'sku' => 'JP-000041',
        'ean' => '6191234567890',
        'shortDescription' => '<p>Nettoie la peau en douceur.</p>',
        'description' => '',
        'regularPrice' => '32.900',
        'salePrice' => '',
        'stockQuantity' => 12,
        'stockStatus' => 'instock',
        'manageStock' => true,
        'status' => 'draft',
        'imageId' => 300,
        'galleryImageIds' => [301, 302],
        'weight' => '0.250',
        'length' => '12',
        'width' => '5',
        'height' => '18',
        'categoryIds' => [8],
        'hasBrand' => true,
        'variation' => false,
    ];

    return new ProductData(...array_replace($data, $overrides));
};

return [
    'accepts a publication-ready canonical product' => static function (TestHarness $test) use ($lookup, $product): void {
        $result = (new ProductDataValidator($lookup()))->validate($product());
        $test->assertSame([], $result->hardErrors());
        $test->assertSame([], $result->publicationErrors());
        $test->assertTrue($result->publicationReady());
    },
    'reports every missing publication field without inventing defaults' => static function (TestHarness $test) use ($lookup, $product): void {
        $result = (new ProductDataValidator($lookup()))->validate($product([
            'name' => ' ',
            'sku' => '',
            'regularPrice' => '',
            'stockQuantity' => null,
            'manageStock' => false,
            'shortDescription' => '<p> </p>',
            'description' => '',
            'imageId' => 0,
            'categoryIds' => [],
            'hasBrand' => false,
        ]));

        foreach (
            [
                'missing_name',
                'missing_sku',
                'missing_regular_price',
                'stock_management_required',
                'missing_stock_quantity',
                'missing_description',
                'missing_brand',
                'missing_category',
                'missing_featured_image',
            ] as $code
        ) {
            $test->assertTrue(in_array($code, $result->publicationErrors(), true), 'Missing ' . $code);
        }
        $test->assertTrue(! $result->publicationReady());
    },
    'rejects duplicate SKU and EAN owned by another product' => static function (TestHarness $test) use ($lookup, $product): void {
        $validator = new ProductDataValidator($lookup(['JP-000041' => 99], ['6191234567890' => 100]));
        $result = $validator->validate($product());
        $test->assertTrue($result->hasHardError('duplicate_sku'));
        $test->assertTrue($result->hasHardError('duplicate_ean'));
    },
    'allows the product to retain its own identifiers during update' => static function (TestHarness $test) use ($lookup, $product): void {
        $validator = new ProductDataValidator($lookup(['JP-000041' => 41], ['6191234567890' => 41]));
        $result = $validator->validate($product());
        $test->assertSame([], $result->hardErrors());
    },
    'rejects negative money stock and package measurements' => static function (TestHarness $test) use ($lookup, $product): void {
        $result = (new ProductDataValidator($lookup()))->validate($product([
            'regularPrice' => '-1',
            'salePrice' => '-0.100',
            'stockQuantity' => -1,
            'weight' => '-0.1',
            'length' => 'not-a-number',
        ]));

        foreach (
            [
                'negative_regular_price',
                'negative_sale_price',
                'negative_stock_quantity',
                'negative_weight',
                'invalid_length',
            ] as $code
        ) {
            $test->assertTrue($result->hasHardError($code), 'Missing ' . $code);
        }
    },
    'rejects on-backorder stock status at launch' => static function (TestHarness $test) use ($lookup, $product): void {
        $result = (new ProductDataValidator($lookup()))->validate($product(['stockStatus' => 'onbackorder']));
        $test->assertTrue($result->hasHardError('invalid_stock_status'));
    },
    'keeps variation validation limited to hard data rules' => static function (TestHarness $test) use ($lookup, $product): void {
        $result = (new ProductDataValidator($lookup()))->validate($product([
            'variation' => true,
            'name' => '',
            'sku' => '',
            'regularPrice' => '',
            'stockQuantity' => null,
            'manageStock' => false,
            'shortDescription' => '',
            'imageId' => 0,
            'categoryIds' => [],
            'hasBrand' => false,
        ]));
        $test->assertSame([], $result->publicationErrors());
    },
];
