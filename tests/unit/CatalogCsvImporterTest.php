<?php

declare(strict_types=1);

use JouvencePara\Core\Catalog\CatalogCsvGateway;
use JouvencePara\Core\Catalog\CatalogCsvImporter;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/CatalogCsvGateway.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/CatalogCsvSchema.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/CatalogCsvImporter.php';

$gateway = static function (array $categories = ['visage' => 1], array $brands = ['avene' => 2], array $existing = []): CatalogCsvGateway {
    return new class($categories, $brands, $existing) implements CatalogCsvGateway {
        public function __construct(private array $categories, private array $brands, private array $existing) {}
        public function categoryId(string $slug): int { return (int) ($this->categories[$slug] ?? 0); }
        public function brandId(string $slug): int { return (int) ($this->brands[$slug] ?? 0); }
        public function productIdBySku(string $sku): int { return (int) ($this->existing[$sku] ?? 0); }
        public function save(array $row, int $productId, int $categoryId, int $brandId): int { return $productId > 0 ? $productId : 99; }
        public function exportPage(int $page, int $perPage): array { return []; }
    };
};
$row = static fn (array $overrides = []): array => array_replace([
    'sku' => 'JP-CSV-1', 'name' => 'Produit', 'ean' => '6191234567890', 'regular_price' => '20.000',
    'sale_price' => '18.000', 'stock_quantity' => '4', 'stock_status' => 'instock', 'short_description' => 'Texte',
    'description' => '', 'brand' => 'avene', 'category' => 'visage', 'image_id' => '10', 'status' => 'draft',
], $overrides);

return [
    'imports a valid controlled row' => static function (TestHarness $test) use ($gateway, $row): void {
        $result = (new CatalogCsvImporter($gateway()))->import([$row()]);
        $test->assertSame('created', $result[0]['status']);
        $test->assertSame(99, $result[0]['product_id']);
    },
    'rejects unknown brand without creating it' => static function (TestHarness $test) use ($gateway, $row): void {
        $result = (new CatalogCsvImporter($gateway()))->import([$row(['brand' => 'unknown'])]);
        $test->assertTrue(in_array('unknown_brand', $result[0]['errors'], true));
    },
    'rejects duplicate SKU and invalid sale and stock' => static function (TestHarness $test) use ($gateway, $row): void {
        $rows = [$row(), $row(['sale_price' => '25.000', 'stock_quantity' => '-1'])];
        $result = (new CatalogCsvImporter($gateway()))->import($rows);
        $test->assertTrue(in_array('duplicate_sku_in_file', $result[1]['errors'], true));
        $test->assertTrue(in_array('sale_price_above_regular_price', $result[1]['errors'], true));
        $test->assertTrue(in_array('invalid_stock_quantity', $result[1]['errors'], true));
    },
    're-import updates an existing SKU' => static function (TestHarness $test) use ($gateway, $row): void {
        $result = (new CatalogCsvImporter($gateway(existing: ['JP-CSV-1' => 44])))->import([$row()]);
        $test->assertSame('updated', $result[0]['status']);
        $test->assertSame(44, $result[0]['product_id']);
    },
];
