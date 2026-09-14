<?php

declare(strict_types=1);

use JouvencePara\Core\Admin\RolePolicy;
use JouvencePara\Core\Security\SecurityPolicy;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Admin/RolePolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Security/SecurityPolicy.php';

return [
    'requires 2FA only for privileged launch roles' => static function (TestHarness $test): void {
        $test->assertTrue(SecurityPolicy::requiresTwoFactor(['administrator']));
        $test->assertTrue(SecurityPolicy::requiresTwoFactor([RolePolicy::STORE_MANAGER]));
        $test->assertTrue(! SecurityPolicy::requiresTwoFactor([RolePolicy::CATALOG_MANAGER]));
    },
    'throttle keys hash identifiers rather than storing raw usernames or IPs' => static function (TestHarness $test): void {
        $key = SecurityPolicy::loginThrottleKey('admin@example.test', '192.0.2.10');
        $test->assertTrue(! str_contains($key, 'admin'));
        $test->assertTrue(! str_contains($key, '192.0.2.10'));
    },
];
