<?php

declare(strict_types=1);

use JouvencePara\Core\Catalog\ProductIdentifier;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/ProductIdentifier.php';

return [
    'normalizes manufacturer barcodes without preserving markup or whitespace' => static function (TestHarness $test): void {
        $test->assertSame('619ABC123', ProductIdentifier::normalizeEan(" <b>619 abc 123</b>\n"));
    },
];
