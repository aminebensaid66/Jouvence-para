<?php

declare(strict_types=1);

use JouvencePara\Core\Admin\MerchandisingBlock;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Admin/MerchandisingBlock.php';

return [
    'accepts only known block types' => static function (TestHarness $test): void {
        $test->assertSame('product', MerchandisingBlock::validType('product'));
        $test->assertSame('banner', MerchandisingBlock::validType('unexpected'));
    },
    'reference is required for entity-backed blocks' => static function (TestHarness $test): void {
        $test->assertTrue(MerchandisingBlock::requiresReference('category'));
        $test->assertTrue(MerchandisingBlock::requiresReference('brand'));
        $test->assertTrue(MerchandisingBlock::requiresReference('product'));
        $test->assertTrue(! MerchandisingBlock::requiresReference('banner'));
    },
];
