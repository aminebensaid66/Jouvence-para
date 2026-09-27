<?php

declare(strict_types=1);

use JouvencePara\Core\Support\DeliveryAdviceModule;
if (! function_exists('add_shortcode')) { function add_shortcode(string $tag, mixed $callback): bool { $GLOBALS['jp_test_shortcodes'][$tag] = $callback; return true; } }
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Support/DeliveryAdvicePolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Support/DeliveryAdviceModule.php';

return [
    'registers policy page and public delivery data hooks' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new DeliveryAdviceModule())->register();
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['jouvence_para_delivery_advice']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['admin_init']));
    },
];
