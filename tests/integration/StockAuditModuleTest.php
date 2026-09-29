<?php

declare(strict_types=1);

use JouvencePara\Core\Audit\AuditModule;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Observability/Redactor.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Audit/AuditEvent.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Audit/AuditRepository.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Audit/AuditModule.php';
require_once __DIR__ . '/seo-fixture.php';
require_once __DIR__ . '/audit-fixture.php';

if (! defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}

return [
    'registers simple and variation stock API hooks and reason fields' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new AuditModule())->register();
        foreach (['woocommerce_product_before_set_stock', 'woocommerce_variation_before_set_stock', 'woocommerce_product_set_stock', 'woocommerce_variation_set_stock', 'woocommerce_after_product_object_save', 'woocommerce_product_options_inventory_product_data', 'woocommerce_variation_options_inventory'] as $hook) {
            $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action'][$hook]), $hook);
        }
    },
    'native CRUD uses persisted old value and records exactly one complete event' => static function (TestHarness $test): void {
        $db = jp_test_audit_reset();
        $module = new AuditModule();
        $product = new WC_Product(['stock_quantity' => 8]);
        $GLOBALS['jp_audit_meta'][41]['_stock'] = '12';
        $_POST = ['jp_stock_reason' => [41 => '<b>Cycle count</b>'], 'jp_stock_reason_nonce' => [41 => 'valid']];
        $module->stockUpdating($product);
        $module->postMetaUpdating(null, 41, '_stock', 8, '');
        $GLOBALS['jp_audit_meta'][41]['_stock'] = '8';
        $module->postMetaUpdated(1, 41, '_stock', 8);
        $module->stockUpdated($product);
        $module->stockUpdated($product);
        $test->assertSame(1, count($db->rows));
        $row = $db->rows[0];
        $test->assertSame(7, $row['actor_id']);
        $test->assertSame('41', $row['object_id']);
        $test->assertSame('product', $row['object_type']);
        $test->assertSame(['field' => '_stock', 'value' => 12], json_decode($row['before_json'], true));
        $test->assertSame(['field' => '_stock', 'value' => 8, 'reason' => 'Cycle count'], json_decode($row['after_json'], true));
        $test->assertTrue(preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $row['occurred_at']) === 1);
        $test->assertSame(8, $product->get_stock_quantity());
        $test->assertSame('32.900', $product->get_regular_price());
    },
    'direct stock API captures variations and each successive real adjustment' => static function (TestHarness $test): void {
        $db = jp_test_audit_reset();
        $module = new AuditModule();
        $product = new WC_Product(['id' => 42, 'type' => 'variation', 'stock_quantity' => 5]);
        $GLOBALS['jp_audit_meta'][42]['_stock'] = '5';
        $module->stockUpdating($product);
        $product->set_stock_quantity(3);
        $GLOBALS['jp_audit_meta'][42]['_stock'] = '3';
        $module->clearUnchangedStock($product);
        $module->stockUpdated($product);
        $module->stockUpdating($product);
        $product->set_stock_quantity(2);
        $module->stockUpdated($product);
        $test->assertSame(2, count($db->rows));
        $test->assertSame('product_variation', $db->rows[0]['object_type']);
        $test->assertSame(3, json_decode($db->rows[0]['after_json'], true)['value']);
        $test->assertSame(3, json_decode($db->rows[1]['before_json'], true)['value']);
    },
    'no-op saves retries and unrelated metadata do not create stock events' => static function (TestHarness $test): void {
        $db = jp_test_audit_reset();
        $module = new AuditModule();
        $product = new WC_Product(['stock_quantity' => 12]);
        $GLOBALS['jp_audit_meta'][41]['_stock'] = '12';
        $module->stockUpdating($product);
        $module->clearUnchangedStock($product);
        $module->stockUpdated($product);
        $module->postMetaUpdating(null, 41, '_stock', 12, '');
        $module->postMetaUpdated(1, 41, '_stock', 12);
        $module->postMetaAdded(1, 99, '_stock', 10);
        $test->assertSame([], $db->rows);
        $module->postMetaUpdating(null, 41, '_stock', 10, '');
        $module->postMetaUpdated(1, 41, '_stock', 10);
        $test->assertSame(1, count($db->rows), 'No stale CRUD marker may swallow a later direct metadata adjustment');
    },
    'legacy metadata stock and variation stock status retain audit coverage' => static function (TestHarness $test): void {
        $db = jp_test_audit_reset();
        $module = new AuditModule();
        $GLOBALS['jp_audit_meta'][42] = ['_stock' => '4', '_stock_status' => 'instock'];
        $module->postMetaUpdating(null, 42, '_stock', 0, '');
        $module->postMetaUpdated(1, 42, '_stock', 0);
        $module->postMetaUpdating(null, 42, '_stock_status', 'outofstock', '');
        $module->postMetaUpdated(2, 42, '_stock_status', 'outofstock');
        $test->assertSame(2, count($db->rows));
        $test->assertSame(4, json_decode($db->rows[0]['before_json'], true)['value']);
        $test->assertSame('product_stock_status_changed', $db->rows[1]['action']);
        $test->assertSame('instock', json_decode($db->rows[1]['before_json'], true)['value']);
    },
    'mixed integer and float quantities remain no-op and release their CRUD marker' => static function (TestHarness $test): void {
        $db = jp_test_audit_reset();
        $module = new AuditModule();
        $product = new WC_Product(['stock_quantity' => 12.0]);
        $GLOBALS['jp_audit_meta'][41]['_stock'] = '12';
        $module->stockUpdating($product);
        $module->clearUnchangedStock($product);
        $module->stockUpdated($product);
        $module->postMetaUpdating(null, 41, '_stock', 12.0, '');
        $module->postMetaUpdated(1, 41, '_stock', 12.0);
        $test->assertSame([], $db->rows);
        $module->postMetaUpdating(null, 41, '_stock', 11.5, '');
        $module->postMetaUpdated(1, 41, '_stock', 11.5);
        $test->assertSame(1, count($db->rows));
        $test->assertSame(11.5, json_decode($db->rows[0]['after_json'], true)['value']);
    },
    'forged reasons are discarded while actual stock changes remain audited' => static function (TestHarness $test): void {
        foreach (['missing', 'invalid', 'denied', 'array', 'other_product'] as $scenario) {
            $db = jp_test_audit_reset();
            $_POST = ['jp_stock_reason' => [41 => 'Count'], 'jp_stock_reason_nonce' => [41 => 'valid']];
            if ($scenario === 'missing') { unset($_POST['jp_stock_reason_nonce']); }
            if ($scenario === 'invalid') { $GLOBALS['jp_audit_nonce_valid'] = false; }
            if ($scenario === 'denied') { $GLOBALS['jp_test_capability'] = false; }
            if ($scenario === 'array') { $_POST['jp_stock_reason'][41] = ['Count']; }
            if ($scenario === 'other_product') { $_POST = ['jp_stock_reason' => [42 => 'Count'], 'jp_stock_reason_nonce' => [42 => 'valid']]; }
            $module = new AuditModule();
            $module->postMetaAdded(1, 41, '_stock', 2);
            $test->assertSame(false, isset(json_decode($db->rows[0]['after_json'], true)['reason']), $scenario);
        }
        $_POST = [];
    },
    'reasons are bounded and redacted and never persisted on the product' => static function (TestHarness $test): void {
        $db = jp_test_audit_reset();
        $_POST = ['jp_stock_reason' => [41 => 'Count user@example.com Bearer abc123 ' . str_repeat('a', 600)], 'jp_stock_reason_nonce' => [41 => 'valid']];
        (new AuditModule())->postMetaAdded(1, 41, '_stock', 2);
        $reason = json_decode($db->rows[0]['after_json'], true)['reason'];
        $test->assertTrue(! str_contains($reason, 'user@example.com'));
        $test->assertTrue(! str_contains($reason, 'abc123'));
        $test->assertTrue(strlen($reason) <= 500);
        $test->assertSame([], $GLOBALS['jp_audit_meta']);
        $_POST = [];
    },
    'Unicode reasons remain valid bounded JSON and zero is distinct from unset stock' => static function (TestHarness $test): void {
        $db = jp_test_audit_reset();
        $_POST = ['jp_stock_reason' => [41 => str_repeat('éع', 300)], 'jp_stock_reason_nonce' => [41 => 'valid']];
        (new AuditModule())->postMetaAdded(1, 41, '_stock', 0);
        $after = json_decode($db->rows[0]['after_json'], true);
        $test->assertSame(str_repeat('éع', 250), $after['reason']);
        $test->assertSame(0, $after['value']);
        $test->assertSame(null, json_decode($db->rows[0]['before_json'], true)['value']);
        $_POST = [];
    },
    'audit repository preserves mixed accented and emoji reasons through repeated redaction' => static function (TestHarness $test): void {
        foreach (['a' . str_repeat('é', 600) => 'a' . str_repeat('é', 499), str_repeat('🙂', 400) => str_repeat('🙂', 250)] as $input => $expected) {
            $db = jp_test_audit_reset();
            $_POST = ['jp_stock_reason' => [41 => $input], 'jp_stock_reason_nonce' => [41 => 'valid']];
            (new AuditModule())->postMetaAdded(1, 41, '_stock', 1);
            $test->assertSame($expected, json_decode($db->rows[0]['after_json'], true)['reason']);
        }
        $_POST = [];
    },
    'reason fields are blank scoped labeled and authorized on simple and variation editors' => static function (TestHarness $test): void {
        jp_test_audit_reset();
        $module = new AuditModule();
        $GLOBALS['product_object'] = new WC_Product();
        $module->renderStockReason();
        $module->renderVariationStockReason(0, [], new WP_Post(['ID' => 42]));
        $test->assertSame('jp_stock_reason[41]', $GLOBALS['jp_audit_fields'][1]['name']);
        $test->assertSame('', $GLOBALS['jp_audit_fields'][1]['value']);
        $test->assertSame('jp_stock_reason[42]', $GLOBALS['jp_audit_fields'][3]['name']);
        $GLOBALS['jp_test_capability'] = false;
        $module->renderStockReason();
        $test->assertSame(4, count($GLOBALS['jp_audit_fields']));
        unset($GLOBALS['product_object']);
    },
    'audit screen rejects unauthorized access before reading and escapes stored details' => static function (TestHarness $test): void {
        $db = jp_test_audit_reset();
        $GLOBALS['jp_test_capability'] = false;
        $module = new AuditModule();
        try {
            $module->renderAdminPage();
            $test->assertTrue(false, 'Expected authorization failure');
        } catch (RuntimeException $exception) {
            $test->assertSame(0, $db->reads);
        }
        $GLOBALS['jp_test_capability'] = true;
        $db->rows[] = ['action' => '<script>alert(1)</script>', 'after_json' => '{"reason":"<img src=x>"}'];
        $html = jp_test_seo_output([$module, 'renderAdminPage']);
        $test->assertTrue(! str_contains($html, '<script>'));
        $test->assertTrue(str_contains($html, '&lt;script&gt;'));
        $test->assertTrue(! str_contains($html, '<img src=x>'));
        unset($GLOBALS['wpdb']);
    },
];
