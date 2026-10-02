<?php

declare(strict_types=1);

use JouvencePara\Core\Privacy\CustomerDataRightsModule;
use JouvencePara\Core\Customers\CommunicationPreferences;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Contracts/Module.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Customers/CommunicationPreferences.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Customers/WishlistPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Privacy/CustomerDataRightsModule.php';
require_once __DIR__ . '/customer-data-rights-fixture.php';

function jp_test_customer_data_rights_reset(): void
{
    $GLOBALS['jp_privacy_hooks'] = [];
    $GLOBALS['jp_privacy_users'] = [
        (object) ['ID' => 12, 'user_email' => 'one@example.test'],
        (object) ['ID' => 34, 'user_email' => 'two@example.test'],
    ];
    $GLOBALS['jp_privacy_meta'] = [
        12 => [
            '_jp_wishlist_product_ids' => [7, '9', 'bad', 0],
            CommunicationPreferences::META_KEY => ['version' => 1, 'email_marketing' => false, 'updated_at' => '2026-09-01T10:20:30Z'],
            '_unrelated_private_data' => 'preserved',
        ],
        34 => ['_jp_wishlist_product_ids' => [77]],
    ];
    $GLOBALS['jp_privacy_delete_failure'] = null;
}

return [
    'registers dedicated exporter and eraser in WordPress privacy tools' => static function (TestHarness $test): void {
        jp_test_customer_data_rights_reset();
        $module = new CustomerDataRightsModule();
        $module->register();
        $test->assertTrue(isset($GLOBALS['jp_privacy_hooks']['wp_privacy_personal_data_exporters']));
        $test->assertTrue(isset($GLOBALS['jp_privacy_hooks']['wp_privacy_personal_data_erasers']));
        $exporters = $module->registerExporter([]);
        $erasers = $module->registerEraser([]);
        $test->assertTrue(is_callable($exporters['jouvence-para-customer-data']['callback']));
        $test->assertTrue(is_callable($erasers['jouvence-para-customer-data']['callback']));
    },
    'exports only the requested email owner wishlist and communication preference' => static function (TestHarness $test): void {
        jp_test_customer_data_rights_reset();
        $result = (new CustomerDataRightsModule())->exportCustomerData('one@example.test');
        $test->assertSame(true, $result['done']);
        $test->assertSame('jouvence-para-customer-12', $result['data'][0]['item_id']);
        $exported = $result['data'][0]['data'];
        $test->assertSame('Disabled', $exported[0]['value']);
        $test->assertSame('2026-09-01T10:20:30Z', $exported[1]['value']);
        $test->assertSame('7, 9', $exported[2]['value']);
        $test->assertTrue(! str_contains(serialize($result), 'two@example.test'));
    },
    'returns no personal data for unknown email addresses and later export pages' => static function (TestHarness $test): void {
        jp_test_customer_data_rights_reset();
        $module = new CustomerDataRightsModule();
        $test->assertSame(['data' => [], 'done' => true], $module->exportCustomerData('missing@example.test'));
        $test->assertSame(['data' => [], 'done' => true], $module->exportCustomerData('one@example.test', 2));
    },
    'approved erasure removes only Jouvence Para preference metadata for the requested user' => static function (TestHarness $test): void {
        jp_test_customer_data_rights_reset();
        $result = (new CustomerDataRightsModule())->eraseCustomerData('one@example.test');
        $test->assertSame(['items_removed' => true, 'items_retained' => false, 'messages' => [], 'done' => true], $result);
        $test->assertSame(['_unrelated_private_data' => 'preserved'], $GLOBALS['jp_privacy_meta'][12]);
        $test->assertSame(['_jp_wishlist_product_ids' => [77]], $GLOBALS['jp_privacy_meta'][34]);
    },
    'reports incomplete erasure without exposing customer data or deleting another owner' => static function (TestHarness $test): void {
        jp_test_customer_data_rights_reset();
        $GLOBALS['jp_privacy_delete_failure'] = '_jp_wishlist_product_ids';
        $result = (new CustomerDataRightsModule())->eraseCustomerData('one@example.test');
        $test->assertSame(true, $result['items_retained']);
        $test->assertSame(true, $result['done']);
        $test->assertTrue(! str_contains(serialize($result), 'one@example.test'));
        $test->assertSame(['_jp_wishlist_product_ids' => [77]], $GLOBALS['jp_privacy_meta'][34]);
    },
];
