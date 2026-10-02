<?php

declare(strict_types=1);

use JouvencePara\Core\Support\WhatsAppModule;
use JouvencePara\Core\Support\WhatsAppService;
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Support/WhatsAppService.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Support/WhatsAppModule.php';

return [
    'registers support URL and settings hooks' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new WhatsAppModule())->register();
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['jouvence_para_whatsapp_url']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['jouvence_para_whatsapp_cart_share_url']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['admin_init']));
    },
    'cart share URL accepts first-party product context and rejects a foreign product URL' => static function (TestHarness $test): void {
        $module = new WhatsAppModule();
        $safe = $module->cartShareUrl('', [['name' => 'Product', 'url' => 'https://store.example/product/', 'quantity' => 1]]);
        $unsafe = $module->cartShareUrl('', [['name' => 'Product', 'url' => 'https://attacker.example/product/', 'quantity' => 1]]);
        $test->assertTrue(str_starts_with($safe, 'https://wa.me/21629302202?text='));
        $test->assertSame('', $unsafe);
    },
];
