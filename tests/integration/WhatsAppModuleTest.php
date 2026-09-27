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
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['admin_init']));
    },
];
