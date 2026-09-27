<?php

declare(strict_types=1);

use JouvencePara\Core\Admin\RolePolicy;
use JouvencePara\Core\Admin\StaffRolesModule;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Admin/RolePolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Admin/StaffRolesModule.php';

return [
    'strips security and payment configuration caps from staff roles' => static function (TestHarness $test): void {
        $user = (object) ['roles' => [RolePolicy::STORE_MANAGER]];
        $caps = ['read' => true, 'activate_plugins' => true, 'manage_woocommerce' => true, 'edit_products' => true];
        $filtered = (new StaffRolesModule())->stripForbiddenCapabilities($caps, [], [], $user);
        $test->assertTrue(isset($filtered['read']));
        $test->assertTrue(isset($filtered['edit_products']));
        $test->assertTrue(! isset($filtered['activate_plugins']));
        $test->assertTrue(! isset($filtered['manage_woocommerce']));
    },
    'does not alter administrator capabilities' => static function (TestHarness $test): void {
        $user = (object) ['roles' => ['administrator']];
        $caps = ['manage_options' => true, 'activate_plugins' => true];
        $test->assertSame($caps, (new StaffRolesModule())->stripForbiddenCapabilities($caps, [], [], $user));
    },
];
