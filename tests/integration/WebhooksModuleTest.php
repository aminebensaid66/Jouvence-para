<?php

declare(strict_types=1);

use JouvencePara\Core\Webhooks\DatabaseEventStore;
use JouvencePara\Core\Webhooks\VerifiedEvent;
use JouvencePara\Core\Webhooks\WebhookAdapter;
use JouvencePara\Core\Webhooks\WebhooksModule;
use JouvencePara\Core\Infrastructure\Migrations\WebhookEventsMigration;
use JouvencePara\Core\Infrastructure\Migrations\Migrator;

foreach (['VerifiedEvent', 'WebhookAdapter', 'EventStore', 'DatabaseEventStore', 'WebhookProcessor', 'WebhooksModule'] as $file) {
    require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Webhooks/' . $file . '.php';
}
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Infrastructure/Migrations/WebhookEventsMigration.php';
require_once __DIR__ . '/webhook-fixture.php';

final class JP_Test_Signed_Webhook_Adapter implements WebhookAdapter
{
    public int $calls = 0;
    public function verify(string $body, array $headers): ?VerifiedEvent
    {
        $timestamp = $headers['timestamp'][0] ?? '';
        $signature = $headers['signature'][0] ?? '';
        if (! ctype_digit($timestamp) || abs(time() - (int) $timestamp) > 300
            || ! hash_equals(hash_hmac('sha256', $timestamp . '.' . $body, 'test-only-key'), $signature)) {
            return null;
        }
        $data = json_decode($body, true);
        return is_array($data) && isset($data['id']) ? new VerifiedEvent($data['id'], 'test.event') : null;
    }
    public function process(VerifiedEvent $event, string $idempotencyKey): string { ++$this->calls; return 'succeeded'; }
}

return [
    'database event claims are unique conflict-aware and preserve terminal states' => static function (TestHarness $test): void {
        $GLOBALS['wpdb'] = new JP_Test_Webhook_Database();
        $store = new DatabaseEventStore();
        $key = hash('sha256', 'evt');
        $hash = hash('sha256', '{}');
        $test->assertSame('claimed', $store->claim($key, 'test', 'test.event', $hash, 'owner-a'));
        $test->assertSame('busy', $store->claim($key, 'test', 'test.event', $hash, 'owner-a'));
        $test->assertSame(true, $store->finish($key, 'succeeded', 'owner-a'));
        $test->assertSame(false, $store->finish($key, 'retryable', 'owner-a'));
        $test->assertSame('succeeded', $store->claim($key, 'test', 'test.event', $hash, 'owner-a'));
        $test->assertSame('conflict', $store->claim($key, 'test', 'test.event', hash('sha256', 'other'), 'owner-a'));
        $test->assertSame(1, count($GLOBALS['wpdb']->rows));
    },
    'retry claim uses compare-and-set while racing requests cannot acquire it' => static function (TestHarness $test): void {
        $GLOBALS['wpdb'] = new JP_Test_Webhook_Database();
        $store = new DatabaseEventStore();
        $key = hash('sha256', 'evt');
        $hash = hash('sha256', '{}');
        $store->claim($key, 'test', 'test.event', $hash, 'owner-a');
        $store->finish($key, 'retryable', 'owner-a');
        $test->assertSame('claimed', $store->claim($key, 'test', 'test.event', $hash, 'owner-a'));
        $test->assertSame(2, $GLOBALS['wpdb']->rows[$key]['attempts']);
        $store->finish($key, 'retryable', 'owner-a');
        $GLOBALS['wpdb']->race = true;
        $test->assertSame('busy', $store->claim($key, 'test', 'test.event', $hash, 'owner-a'));
        $test->assertTrue(str_contains(implode("\n", $GLOBALS['wpdb']->sql), "AND state = 'retryable' AND payload_hash = %s"));
        $GLOBALS['wpdb']->fail = true;
        $test->assertSame('unavailable', $store->claim($key, 'test', 'test.event', $hash, 'owner-a'));
        $test->assertSame(false, $store->finish($key, 'succeeded', 'owner-a'));
    },
    'routes stay absent until an explicit adapter is configured' => static function (TestHarness $test): void {
        $GLOBALS['jp_webhook_routes'] = [];
        $GLOBALS['jp_webhook_adapters'] = [];
        $module = new WebhooksModule();
        $module->registerRoutes();
        $test->assertSame([], $GLOBALS['jp_webhook_routes']);
        $adapter = new JP_Test_Signed_Webhook_Adapter();
        $GLOBALS['jp_webhook_adapters'] = ['unsafe/path' => $adapter, 'missing' => new stdClass(), 'test' => $adapter];
        $module->registerRoutes();
        $test->assertSame(1, count($GLOBALS['jp_webhook_routes']));
        $test->assertSame('POST', $GLOBALS['jp_webhook_routes']['jouvence-para/v1/webhooks/test']['methods']);
    },
    'stale attempt completion cannot overwrite a later retry owner' => static function (TestHarness $test): void {
        $GLOBALS['wpdb'] = new JP_Test_Webhook_Database();
        $store = new DatabaseEventStore();
        $key = hash('sha256', 'evt');
        $hash = hash('sha256', '{}');
        $store->claim($key, 'test', 'test.event', $hash, 'owner-a');
        $store->finish($key, 'retryable', 'owner-a');
        $test->assertSame('claimed', $store->claim($key, 'test', 'test.event', $hash, 'owner-b'));
        $test->assertSame(false, $store->finish($key, 'succeeded', 'owner-a'));
        $test->assertSame('processing', $GLOBALS['wpdb']->rows[$key]['state']);
        $test->assertSame(true, $store->finish($key, 'succeeded', 'owner-b'));
    },
    'REST callback rejects unsigned stale tampered requests and suppresses signed duplicates' => static function (TestHarness $test): void {
        $GLOBALS['wpdb'] = new JP_Test_Webhook_Database();
        $GLOBALS['jp_webhook_routes'] = [];
        $adapter = new JP_Test_Signed_Webhook_Adapter();
        $GLOBALS['jp_webhook_adapters'] = ['test' => $adapter];
        (new WebhooksModule())->registerRoutes();
        $callback = $GLOBALS['jp_webhook_routes']['jouvence-para/v1/webhooks/test']['callback'];
        $body = '{"id":"evt-1","email":"never-store@example.com"}';
        $timestamp = (string) time();
        $headers = ['timestamp' => [$timestamp], 'signature' => [hash_hmac('sha256', $timestamp . '.' . $body, 'test-only-key')]];
        $test->assertSame(401, $callback(new WP_REST_Request($body))->status);
        $test->assertSame(401, $callback(new WP_REST_Request($body . ' ', $headers))->status);
        $stale = (string) (time() - 301);
        $test->assertSame(401, $callback(new WP_REST_Request($body, ['timestamp' => [$stale], 'signature' => [hash_hmac('sha256', $stale . '.' . $body, 'test-only-key')]]))->status);
        $test->assertSame(200, $callback(new WP_REST_Request($body, $headers))->status);
        $test->assertSame(['state' => 'succeeded'], $callback(new WP_REST_Request($body, $headers))->data);
        $test->assertSame(1, $adapter->calls);
        $test->assertTrue(! str_contains(json_encode($GLOBALS['wpdb']->rows), 'never-store'));
        unset($GLOBALS['wpdb']);
    },
    'schema migration is repeatable and failed creation never advances the version' => static function (TestHarness $test): void {
        $GLOBALS['wpdb'] = new JP_Test_Webhook_Database();
        $GLOBALS['jp_test_options'] = [Migrator::SCHEMA_VERSION_OPTION => '2'];
        $GLOBALS['jp_test_failed_update'] = null;
        $GLOBALS['jp_webhook_schema_fail'] = false;
        $migration = new WebhookEventsMigration();
        $migration->up();
        $migration->up();
        $test->assertTrue(str_contains($GLOBALS['jp_webhook_schema_sql'], 'PRIMARY KEY  (event_key)'));
        (new Migrator([$migration]))->migrate();
        $test->assertSame('3', get_option(Migrator::SCHEMA_VERSION_OPTION));
        $GLOBALS['jp_test_options'] = [Migrator::SCHEMA_VERSION_OPTION => '2'];
        $GLOBALS['jp_webhook_schema_fail'] = true;
        try { (new Migrator([$migration]))->migrate(); $test->assertTrue(false); } catch (RuntimeException) {
            $test->assertSame('2', get_option(Migrator::SCHEMA_VERSION_OPTION));
        }
        $GLOBALS['jp_webhook_schema_fail'] = false;
        unset($GLOBALS['wpdb']);
    },
];
