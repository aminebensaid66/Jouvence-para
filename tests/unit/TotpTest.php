<?php

declare(strict_types=1);

use JouvencePara\Core\Security\Totp;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Security/Totp.php';

return [
    'matches the RFC6238 SHA1 test vector' => static function (TestHarness $test): void {
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
        $test->assertSame('94287082', Totp::code($secret, 59, 8));
    },
    'verifies six digit codes only inside the accepted time window' => static function (TestHarness $test): void {
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
        $code = Totp::code($secret, 1700000000);
        $test->assertTrue(Totp::verify($secret, $code, 1700000000));
        $test->assertTrue(! Totp::verify($secret, '000000', 1700000000));
    },
    'generates nonempty base32 secrets without logging material' => static function (TestHarness $test): void {
        $secret = Totp::generateSecret();
        $test->assertTrue(strlen($secret) >= 26);
        $test->assertTrue(preg_match('/^[A-Z2-7]+$/', $secret) === 1);
    },
];
