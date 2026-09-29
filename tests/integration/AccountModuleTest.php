<?php

declare(strict_types=1);

use JouvencePara\Core\Customers\AccountModule;
use JouvencePara\Core\Customers\CommunicationPreferences as Preferences;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Contracts/Module.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Customers/CommunicationPreferences.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Customers/AccountModule.php';
require_once __DIR__ . '/account-fixture.php';
require_once __DIR__ . '/theme-fixture.php';
require_once __DIR__ . '/seo-fixture.php';

return [
    'registers native account hooks without replacing authentication or address handlers' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new AccountModule())->register();
        foreach (['woocommerce_edit_account_form', 'woocommerce_save_account_details_errors', 'woocommerce_save_account_details'] as $hook) {
            $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action'][$hook]));
        }
        $test->assertTrue(! isset($GLOBALS['jp_test_hooks']['filter']['authenticate']));
    },
    'authorized setup creates only missing native account page and repeats safely' => static function (TestHarness $test): void {
        jp_test_account_reset();
        $GLOBALS['jp_account_page'] = -1;
        $module = new AccountModule();
        $module->configureWooCommerce();
        $module->configureWooCommerce();
        $test->assertSame(1, count($GLOBALS['jp_account_created']));
        $test->assertSame(['mon-compte', 'woocommerce_myaccount_page_id', 'Mon compte', '[woocommerce_my_account]'], $GLOBALS['jp_account_created'][0]);
        $test->assertSame('yes', get_option('woocommerce_enable_myaccount_registration'));
        $test->assertSame('yes', get_option('woocommerce_enable_guest_checkout'));
        $test->assertSame('1', get_option('jp_account_settings_version'));
    },
    'configuration requires admin capability and never advances after missing page or setting failure' => static function (TestHarness $test): void {
        jp_test_account_reset();
        $module = new AccountModule();
        $GLOBALS['jp_test_capability'] = false;
        $module->configureWooCommerce();
        $test->assertSame([], $GLOBALS['jp_test_options']);
        $GLOBALS['jp_test_capability'] = true;
        $GLOBALS['jp_account_page_status'] = 'private';
        $module->configureWooCommerce();
        $test->assertSame(false, get_option('jp_account_settings_version'));
        $test->assertSame(1, count($GLOBALS['jp_account_errors']));
        $GLOBALS['jp_account_page_status'] = 'publish';
        $GLOBALS['jp_test_failed_update'] = 'woocommerce_enable_myaccount_registration';
        $module->configureWooCommerce();
        $test->assertSame(false, get_option('jp_account_settings_version'));
        $GLOBALS['jp_test_failed_update'] = null;
    },
    'explicit owner choice persists privately and duplicate callbacks do not rewrite timestamps' => static function (TestHarness $test): void {
        jp_test_account_reset();
        jp_test_account_request();
        $module = new AccountModule();
        $errors = new WP_Error();
        $module->validatePreferences($errors, (object) ['ID' => 7]);
        $module->savePreferences(7);
        $module->savePreferences(7);
        $record = $GLOBALS['jp_account_meta'][7][Preferences::META_KEY];
        $test->assertSame([], $errors->errors);
        $test->assertSame(true, Preferences::emailEnabled($record));
        $test->assertSame(1, $GLOBALS['jp_account_saves']);
        $module->validatePreferences($errors, (object) ['ID' => 7]);
        $module->savePreferences(7);
        $test->assertSame(1, $GLOBALS['jp_account_saves']);
        $test->assertSame($record, $GLOBALS['jp_account_meta'][7][Preferences::META_KEY]);
    },
    'published wrong or empty account assignments cannot mark native account setup complete' => static function (TestHarness $test): void {
        foreach (['wrong_type', 'empty_content'] as $scenario) {
            jp_test_account_reset();
            if ($scenario === 'wrong_type') { $GLOBALS['jp_account_page_type'] = 'post'; }
            if ($scenario === 'empty_content') { $GLOBALS['jp_account_page_content'] = 'Existing content'; }
            (new AccountModule())->configureWooCommerce();
            $test->assertSame(false, get_option('jp_account_settings_version'), $scenario);
            $test->assertSame([], $GLOBALS['jp_account_created'], 'Never overwrite an existing assigned page');
            $test->assertSame(1, count($GLOBALS['jp_account_errors']));
        }
    },
    'withdrawal persists and keep or absent controls never grant marketing consent' => static function (TestHarness $test): void {
        jp_test_account_reset();
        $module = new AccountModule();
        $errors = new WP_Error();
        $module->validatePreferences($errors, (object) ['ID' => 7]);
        $module->savePreferences(7);
        jp_test_account_request('keep');
        $module->validatePreferences($errors, (object) ['ID' => 7]);
        $module->savePreferences(7);
        $test->assertSame([], $GLOBALS['jp_account_meta']);
        jp_test_account_request('enable');
        $module->validatePreferences($errors, (object) ['ID' => 7]);
        $module->savePreferences(7);
        jp_test_account_request('disable');
        $module->validatePreferences($errors, (object) ['ID' => 7]);
        $module->savePreferences(7);
        $test->assertSame(false, Preferences::emailEnabled($GLOBALS['jp_account_meta'][7][Preferences::META_KEY]));
        $test->assertSame(2, $GLOBALS['jp_account_saves']);
    },
    'missing invalid nonce malformed fields and cross-user writes are rejected' => static function (TestHarness $test): void {
        foreach (['anonymous', 'other_user', 'nonce', 'native_nonce', 'missing', 'array', 'invalid', 'action'] as $scenario) {
            jp_test_account_reset();
            jp_test_account_request();
            $id = 7;
            if ($scenario === 'anonymous') { $GLOBALS['jp_account_actor'] = 0; }
            if ($scenario === 'other_user') { $id = 8; }
            if ($scenario === 'nonce') { $GLOBALS['jp_account_nonce_valid'] = false; }
            if ($scenario === 'native_nonce') { $GLOBALS['jp_account_native_nonce_valid'] = false; }
            if ($scenario === 'missing') { unset($_POST['jp_account_preferences_nonce']); }
            if ($scenario === 'array') { $_POST['jp_email_marketing_choice'] = ['enable']; }
            if ($scenario === 'invalid') { $_POST['jp_email_marketing_choice'] = 'arbitrary'; }
            if ($scenario === 'action') { $_POST['action'] = 'other'; }
            $module = new AccountModule();
            $errors = new WP_Error();
            $module->validatePreferences($errors, (object) ['ID' => $id]);
            $module->savePreferences($id);
            $test->assertTrue(isset($errors->errors['jp_preferences_invalid']), $scenario);
            $test->assertSame([], $GLOBALS['jp_account_meta'], $scenario);
        }
        $_POST = [];
    },
    'prevalidated choice survives native password session rotation but cannot follow user changes' => static function (TestHarness $test): void {
        jp_test_account_reset();
        jp_test_account_request();
        $module = new AccountModule();
        $module->validatePreferences(new WP_Error(), (object) ['ID' => 7]);
        $GLOBALS['jp_account_nonce_valid'] = false;
        $_POST['jp_email_marketing_choice'] = 'disable';
        $module->savePreferences(7);
        $test->assertSame(true, Preferences::emailEnabled($GLOBALS['jp_account_meta'][7][Preferences::META_KEY]));
        jp_test_account_reset();
        jp_test_account_request();
        $module->validatePreferences(new WP_Error(), (object) ['ID' => 7]);
        $GLOBALS['jp_account_actor'] = 8;
        $module->savePreferences(7);
        $test->assertSame([], $GLOBALS['jp_account_meta']);
    },
    'native save errors and persistence failure never claim preference success' => static function (TestHarness $test): void {
        jp_test_account_reset();
        jp_test_account_request();
        $module = new AccountModule();
        $module->validatePreferences(new WP_Error(), (object) ['ID' => 7]);
        $GLOBALS['jp_account_notices']['error'] = ['native error'];
        $module->savePreferences(7);
        $test->assertSame(0, $GLOBALS['jp_account_saves']);
        $GLOBALS['jp_account_notices'] = [];
        $module->validatePreferences(new WP_Error(), (object) ['ID' => 7]);
        $GLOBALS['jp_account_save_fail'] = true;
        $module->savePreferences(7);
        $test->assertSame([], $GLOBALS['jp_account_meta']);
        $test->assertSame(1, count($GLOBALS['jp_account_notices']['error']));
    },
    'preference form is owner-bound defaults to keep and does not preselect a marketing grant' => static function (TestHarness $test): void {
        jp_test_account_reset();
        $module = new AccountModule();
        $html = jp_test_seo_output([$module, 'renderPreferences']);
        $test->assertTrue(str_contains($html, 'pas d’offres par e-mail'));
        $test->assertSame('keep', $GLOBALS['jp_account_fields']['jp_email_marketing_choice'][1]);
        $test->assertSame(['jp_account_preferences_7', 'jp_account_preferences_nonce'], $GLOBALS['jp_account_fields']['nonce']);
        $GLOBALS['jp_account_actor'] = 0;
        $test->assertSame('', jp_test_seo_output([$module, 'renderPreferences']));
    },
    'theme account link uses published native page and safely escapes its URL' => static function (TestHarness $test): void {
        jp_test_account_reset();
        jp_test_theme_hooks();
        $GLOBALS['jp_account_url'] = 'https://store.example/mon-compte/?a=1&b=2';
        $html = JouvencePara\Theme\account_link_markup();
        $test->assertTrue(str_contains($html, '?a=1&amp;b=2'));
        $test->assertTrue(str_contains($html, '>Mon compte</a>'));
        $GLOBALS['jp_account_page_status'] = 'private';
        $test->assertSame('', JouvencePara\Theme\account_link_markup());
        $GLOBALS['jp_account_page_status'] = 'publish';
        $GLOBALS['jp_account_page'] = -1;
        $test->assertSame('', JouvencePara\Theme\account_link_markup());
        unset($GLOBALS['jp_account_url']);
    },
];
