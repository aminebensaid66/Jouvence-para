<?php

declare(strict_types=1);

use JouvencePara\Core\Catalog\CatalogCsvModule;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Contracts/Module.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/CatalogCsvSchema.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/CatalogCsvGateway.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/CatalogCsvImporter.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/CatalogCsvModule.php';

return [
    'registers nonce-protected import and export handlers' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new CatalogCsvModule())->register();
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['admin_post_jp_catalog_csv_import']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['admin_post_jp_catalog_csv_export']));
    },
    'neutralizes spreadsheet formulas in exported cells' => static function (TestHarness $test): void {
        $test->assertSame("'=HYPERLINK(\"https://example.test\")", CatalogCsvModule::safeCsvCell('=HYPERLINK("https://example.test")'));
        $test->assertSame("'+21629302202", CatalogCsvModule::safeCsvCell('+21629302202'));
        $test->assertSame('ordinary value', CatalogCsvModule::safeCsvCell('ordinary value'));
    },
];
