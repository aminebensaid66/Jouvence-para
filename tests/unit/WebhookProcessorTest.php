<?php

declare(strict_types=1);

use JouvencePara\Core\Webhooks\EventStore;
use JouvencePara\Core\Webhooks\VerifiedEvent;
use JouvencePara\Core\Webhooks\WebhookAdapter;
use JouvencePara\Core\Webhooks\WebhookProcessor;

foreach (['VerifiedEvent', 'WebhookAdapter', 'EventStore', 'WebhookProcessor'] as $file) {
    require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Webhooks/' . $file . '.php';
}

final class JP_Test_Webhook_Adapter implements WebhookAdapter
{
    public bool $verified = true;
    public bool $throws = false;
    public string $result = 'succeeded';
    public int $calls = 0;
    public array $keys = [];
    public function verify(string $body, array $headers): ?VerifiedEvent
    {
        return $this->verified ? new VerifiedEvent('evt-1', 'shipment.updated', ['secret' => 'never-stored']) : null;
    }
    public function process(VerifiedEvent $event, string $idempotencyKey): string
    {
        ++$this->calls;
        $this->keys[] = $idempotencyKey;
        if ($this->throws) { throw new RuntimeException('secret-provider-message'); }
        return $this->result;
    }
}

final class JP_Test_Event_Store implements EventStore
{
    public array $rows = [];
    public bool $available = true;
    public bool $persist = true;
    public function claim(string $key, string $provider, string $type, string $payloadHash, string $owner): string
    {
        if (! $this->available) { return 'unavailable'; }
        if (isset($this->rows[$key])) {
            if ($this->rows[$key]['hash'] !== $payloadHash) { return 'conflict'; }
            if ($this->rows[$key]['state'] !== 'retryable') {
                return in_array($this->rows[$key]['state'], ['succeeded', 'permanent'], true) ? $this->rows[$key]['state'] : 'busy';
            }
        }
        $this->rows[$key] = ['provider' => $provider, 'type' => $type, 'hash' => $payloadHash, 'owner' => $owner, 'state' => 'processing'];
        return 'claimed';
    }
    public function finish(string $key, string $state, string $owner): bool
    {
        if (! $this->persist || $this->rows[$key]['owner'] !== $owner) { return false; }
        $this->rows[$key]['state'] = $state;
        return true;
    }
}

return [
    'verified duplicate events apply side effects once with a stable provider-scoped key' => static function (TestHarness $test): void {
        $store = new JP_Test_Event_Store();
        $adapter = new JP_Test_Webhook_Adapter();
        $processor = new WebhookProcessor($store);
        $test->assertSame(['state' => 'succeeded', 'status' => 200], $processor->handle('carrier', $adapter, '{}', []));
        $processor->handle('carrier', $adapter, '{}', []);
        $test->assertSame(1, $adapter->calls);
        $processor->handle('payment', $adapter, '{}', []);
        $test->assertSame(2, $adapter->calls);
        $test->assertTrue($adapter->keys[0] !== $adapter->keys[1]);
        $test->assertTrue(! str_contains(json_encode($store->rows), 'never-stored'));
    },
    'invalid signatures identifiers and oversized bodies never claim or process events' => static function (TestHarness $test): void {
        $store = new JP_Test_Event_Store();
        $adapter = new JP_Test_Webhook_Adapter();
        $adapter->verified = false;
        $processor = new WebhookProcessor($store);
        $test->assertSame(401, $processor->handle('carrier', $adapter, '{}', [])['status']);
        $test->assertSame(400, $processor->handle('../unsafe', $adapter, '{}', [])['status']);
        $test->assertSame(400, $processor->handle('carrier', $adapter, str_repeat('a', 65537), [])['status']);
        $test->assertSame([], $store->rows);
        $test->assertSame(0, $adapter->calls);
        foreach (['', "evt\n", str_repeat('x', 192)] as $id) {
            try { new VerifiedEvent($id, 'type'); $test->assertTrue(false); } catch (InvalidArgumentException) { $test->assertTrue(true); }
        }
    },
    'same event ID with different signed payload is rejected' => static function (TestHarness $test): void {
        $store = new JP_Test_Event_Store();
        $adapter = new JP_Test_Webhook_Adapter();
        $processor = new WebhookProcessor($store);
        $processor->handle('carrier', $adapter, '{"value":1}', []);
        $test->assertSame(['state' => 'conflict', 'status' => 409], $processor->handle('carrier', $adapter, '{"value":2}', []));
        $test->assertSame(1, $adapter->calls);
    },
    'safe retryable failures reuse the key while permanent failures never replay' => static function (TestHarness $test): void {
        foreach (['retryable', 'permanent'] as $state) {
            $store = new JP_Test_Event_Store();
            $adapter = new JP_Test_Webhook_Adapter();
            $adapter->result = $state;
            $processor = new WebhookProcessor($store);
            $test->assertSame($state, $processor->handle('carrier', $adapter, '{}', [])['state']);
            $adapter->result = 'succeeded';
            $processor->handle('carrier', $adapter, '{}', []);
            $test->assertSame($state === 'retryable' ? 2 : 1, $adapter->calls);
            if ($state === 'retryable') { $test->assertSame($adapter->keys[0], $adapter->keys[1]); }
        }
    },
    'exceptions and invalid outcomes quarantine unknown effects without leaking exception text' => static function (TestHarness $test): void {
        foreach ([true, false] as $throws) {
            $store = new JP_Test_Event_Store();
            $adapter = new JP_Test_Webhook_Adapter();
            $adapter->throws = $throws;
            $adapter->result = 'unexpected';
            $processor = new WebhookProcessor($store);
            $test->assertSame(['state' => 'uncertain', 'status' => 503], $processor->handle('carrier', $adapter, '{}', []));
            $processor->handle('carrier', $adapter, '{}', []);
            $test->assertSame(1, $adapter->calls);
            $test->assertTrue(! str_contains(json_encode($store->rows), 'secret-provider-message'));
        }
    },
    'claim failure blocks effects and completion persistence failure cannot replay effects' => static function (TestHarness $test): void {
        $store = new JP_Test_Event_Store();
        $adapter = new JP_Test_Webhook_Adapter();
        $processor = new WebhookProcessor($store);
        $store->available = false;
        $test->assertSame(503, $processor->handle('carrier', $adapter, '{}', [])['status']);
        $test->assertSame(0, $adapter->calls);
        $store->available = true;
        $store->persist = false;
        $test->assertSame(503, $processor->handle('carrier', $adapter, '{}', [])['status']);
        $processor->handle('carrier', $adapter, '{}', []);
        $test->assertSame(1, $adapter->calls);
    },
];
