<?php

declare(strict_types=1);

use JouvencePara\Core\Payments\PaymentRecord;
use JouvencePara\Core\Payments\WooCommercePaymentRecordStore;
use JouvencePara\Core\Observability\Redactor;

foreach (['PaymentRecord', 'PaymentRecordStore', 'WooCommercePaymentRecordStore'] as $class) {
    require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Payments/' . $class . '.php';
}
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Observability/Redactor.php';

class WC_Order
{
    public array $meta = [];
    public int $saves = 0;
    public string $status = 'processing';

    public function meta_exists(string $key): bool
    {
        return array_key_exists($key, $this->meta);
    }

    public function get_meta(string $key, bool $single = true, string $context = 'view'): mixed
    {
        return $this->meta[$key] ?? '';
    }

    public function update_meta_data(string $key, mixed $value): void
    {
        $this->meta[$key] = $value;
    }

    public function save_meta_data(): void
    {
        $this->saves++;
    }
}

class WC_Order_Refund extends WC_Order
{
}

function wc_get_order(int $id): WC_Order|false
{
    return $GLOBALS['jp_payment_test_orders'][$id] ?? false;
}

return [
    'persists independent records and replays without duplicate writes' => static function (TestHarness $test): void {
        $order = new WC_Order();
        $GLOBALS['jp_payment_test_orders'] = [123 => $order];
        $store = new WooCommercePaymentRecordStore();
        $data = ['method' => 'cod', 'status' => 'unpaid', 'provider' => null, 'transaction_id' => null, 'paid_at' => null, 'refund_status' => 'none'];
        $test->assertSame(null, $store->find(123, 'attempt_1'));
        $record = PaymentRecord::fromArray($data);
        $store->save(123, 'attempt_1', $record);
        $store->save(123, 'attempt_1', $record);
        $test->assertSame(1, $order->saves);
        $test->assertSame('processing', $order->status);
        $test->assertSame($data, $store->find(123, 'attempt_1')->toArray());
        $store->save(123, 'attempt_2', $record);
        $test->assertSame(2, count($order->meta));
        $data['status'] = 'collected';
        $store->save(123, 'attempt_1', PaymentRecord::fromArray($data));
        $test->assertSame('collected', $store->find(123, 'attempt_1')->toArray()['status']);
        $test->assertSame('unpaid', $store->find(123, 'attempt_2')->toArray()['status']);
        $test->assertSame('processing', $order->status);
    },
    'rejects missing orders refunds invalid identifiers and corrupt metadata' => static function (TestHarness $test): void {
        $order = new WC_Order();
        $order->meta['_jp_payment_record_corrupt'] = ['card_number' => 'sensitive-value'];
        $GLOBALS['jp_payment_test_orders'] = [123 => $order, 124 => new WC_Order_Refund()];
        $store = new WooCommercePaymentRecordStore();
        foreach ([[0, 'one'], [999, 'one'], [124, 'one'], [123, '../bad'], [123, 'corrupt']] as [$id, $key]) {
            try {
                $store->find($id, $key);
                $test->assertTrue(false, 'Invalid payment read was accepted.');
            } catch (InvalidArgumentException $error) {
                $test->assertTrue(! str_contains($error->getMessage(), 'sensitive-value'));
            }
        }
        $order->meta['_jp_payment_record_corrupt'] = 'sensitive-value';
        try {
            $store->find(123, 'corrupt');
            $test->assertTrue(false, 'Corrupt metadata was accepted.');
        } catch (RuntimeException $error) {
            $test->assertSame('Invalid stored payment record.', $error->getMessage());
        }
    },
    'redacts provider references payloads and card verification values' => static function (TestHarness $test): void {
        $result = Redactor::context(['provider' => 'test', 'nested' => ['transaction_id' => 'txn_secret', 'providerResponse' => ['anything' => 'private'], 'cvv' => '123', 'payment_record' => ['transaction_id' => 'private']]]);
        $test->assertSame('test', $result['provider']);
        foreach ($result['nested'] as $value) {
            $test->assertSame('[redacted]', $value);
        }
    },
];
