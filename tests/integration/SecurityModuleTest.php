<?php

declare(strict_types=1);

use JouvencePara\Core\Security\SecurityModule;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Security/Totp.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Security/SecurityPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Security/SecurityModule.php';

return [
    'disables XML-RPC at the application layer' => static function (TestHarness $test): void {
        $test->assertSame(false, (new SecurityModule())->disableXmlRpc(true));
    },
    'denies only file-editor context while preserving other file policy decisions' => static function (TestHarness $test): void {
        $module = new SecurityModule();
        $test->assertSame(false, $module->disallowFileEditors(true, 'capability_edit_themes'));
        $test->assertSame(true, $module->disallowFileEditors(true, 'download_language_pack'));
    },
    'registers authentication enrollment and hardening hooks' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new SecurityModule())->register();
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['login_form']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['wp_authenticate_user']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['file_mod_allowed']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['xmlrpc_enabled']));
    },
];
