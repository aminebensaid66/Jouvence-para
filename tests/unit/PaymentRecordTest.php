<?php

declare(strict_types=1);

use JouvencePara\Core\Payments\PaymentRecord;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Payments/PaymentRecord.php';

function jp_test_payment_data(): array
{
    return ['method' => 'cod', 'status' => 'pending', 'provider' => null, 'transaction_id' => null, 'paid_at' => null, 'refund_status' => 'none'];
}

return [
    'round trips COD and provider records without order state' => static function (TestHarness $test): void {
        $data = jp_test_payment_data();
        $test->assertSame($data, PaymentRecord::fromArray($data)->toArray());
        $data = array_replace($data, ['method' => 'online', 'status' => 'paid', 'provider' => 'test_provider', 'transaction_id' => 'txn_123', 'paid_at' => '2026-09-29T12:30:00Z', 'refund_status' => 'partial']);
        $record = PaymentRecord::fromArray($data);
        $test->assertSame($data, $record->toArray());
        $test->assertSame(['method', 'status', 'provider', 'refund_status'], array_keys($record->logContext()));
        $copy = $record->toArray();
        $copy['status'] = 'changed';
        $test->assertSame('paid', $record->toArray()['status']);
    },
    'rejects card fields malformed types and invalid timestamps with safe errors' => static function (TestHarness $test): void {
        $invalid = [
            array_merge(jp_test_payment_data(), ['card_number' => 'sensitive-value']),
            array_merge(jp_test_payment_data(), ['provider_payload' => ['token' => 'sensitive-value']]),
            array_merge(jp_test_payment_data(), ['order_status' => 'completed']),
            array_replace(jp_test_payment_data(), ['status' => ['paid']]),
            array_replace(jp_test_payment_data(), ['provider' => '']),
            array_replace(jp_test_payment_data(), ['transaction_id' => 'sensitive-value']),
            array_replace(jp_test_payment_data(), ['provider' => 'test', 'transaction_id' => "unsafe\nreference"]),
            array_replace(jp_test_payment_data(), ['paid_at' => '2026-02-30T12:00:00Z']),
            array_replace(jp_test_payment_data(), ['paid_at' => '2026-09-29T12:00:00+00:00']),
            array_replace(jp_test_payment_data(), ['refund_status' => null]),
            ['method' => 'cod'],
        ];
        foreach ($invalid as $data) {
            try {
                PaymentRecord::fromArray($data);
                $test->assertTrue(false, 'Invalid payment data was accepted.');
            } catch (InvalidArgumentException $error) {
                $test->assertTrue(! str_contains($error->getMessage(), 'sensitive-value'));
            }
        }
    },
];
