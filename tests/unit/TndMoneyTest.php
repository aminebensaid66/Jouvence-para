<?php

declare(strict_types=1);

use JouvencePara\Core\Catalog\TndMoney;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/TndMoney.php';

return [
    'normalizes TND to three decimal places without floats' => static function (TestHarness $test): void {
        $test->assertSame('12.000', TndMoney::normalize('12'));
        $test->assertSame('0.500', TndMoney::normalize('0,5'));
        $test->assertSame('12.345', TndMoney::normalize('12.3454'));
        $test->assertSame('12.346', TndMoney::normalize('12.3455'));
        $test->assertSame('1000.000', TndMoney::normalize('999.9995'));
    },
    'preserves negative values for validation and rejects malformed values' => static function (TestHarness $test): void {
        $test->assertSame('-1.250', TndMoney::normalize('-1.25'));
        $test->assertTrue(TndMoney::isNegative('-0.001'));
        $test->assertSame(null, TndMoney::normalize('12 TND'));
        $test->assertSame('', TndMoney::normalize(''));
    },
];
