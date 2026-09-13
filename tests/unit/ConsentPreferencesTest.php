<?php

declare(strict_types=1);

use JouvencePara\Core\Privacy\ConsentPreferences;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Privacy/ConsentPreferences.php';

return [
    'round trips valid preferences' => static function (TestHarness $test): void {
        $encoded = ConsentPreferences::encode(['analytics' => true, 'marketing' => false]);
        $decoded = ConsentPreferences::decode($encoded);
        $test->assertSame(['v' => 1, 'analytics' => true, 'marketing' => false], $decoded);
    },
    'fails closed for malformed preferences' => static function (TestHarness $test): void {
        $test->assertSame(null, ConsentPreferences::decode('not-a-valid-cookie'));
        $test->assertTrue(! ConsentPreferences::allows('analytics', 'not-a-valid-cookie'));
    },
    'always allows essential and rejects unknown categories' => static function (TestHarness $test): void {
        $test->assertTrue(ConsentPreferences::allows('essential', ''));
        $test->assertTrue(! ConsentPreferences::allows('unknown', ''));
    },
];
