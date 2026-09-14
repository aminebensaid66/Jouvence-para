<?php

declare(strict_types=1);

use JouvencePara\Core\Admin\RolePolicy;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Admin/RolePolicy.php';

return [
    'defines four operational roles without administrator capabilities' => static function (TestHarness $test): void {
        $roles = RolePolicy::definitions();
        $test->assertSame(4, count($roles));
        foreach ($roles as $role) {
            $test->assertTrue(! isset($role['capabilities']['manage_options']));
            $test->assertTrue(! isset($role['capabilities']['activate_plugins']));
            $test->assertTrue(! isset($role['capabilities']['manage_woocommerce']));
        }
    },
    'catalog role can manage controlled catalog terms but not orders' => static function (TestHarness $test): void {
        $caps = RolePolicy::definitions()[RolePolicy::CATALOG_MANAGER]['capabilities'];
        $test->assertTrue(isset($caps['manage_product_terms']));
        $test->assertTrue(! isset($caps['edit_shop_orders']));
    },
    'order and support roles do not receive refund authorization' => static function (TestHarness $test): void {
        foreach ([RolePolicy::ORDER_OPERATOR, RolePolicy::SUPPORT_OPERATOR] as $slug) {
            $test->assertTrue(! isset(RolePolicy::definitions()[$slug]['capabilities']['jp_process_refunds']));
        }
    },
];
