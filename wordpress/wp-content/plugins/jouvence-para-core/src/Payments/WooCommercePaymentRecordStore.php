<?php

declare(strict_types=1);

namespace JouvencePara\Core\Payments;

use InvalidArgumentException;
use RuntimeException;

/** HPOS-compatible storage, with no payment_complete() or order-state side effects. */
final class WooCommercePaymentRecordStore implements PaymentRecordStore
{
    public function find(int $orderId, string $recordId): ?PaymentRecord
    {
        $key = $this->key($recordId);
        $order = $this->order($orderId);
        if (! $order->meta_exists($key)) {
            return null;
        }
        $data = $order->get_meta($key, true, 'edit');
        if (! is_array($data)) {
            throw new RuntimeException('Invalid stored payment record.');
        }
        return PaymentRecord::fromArray($data);
    }

    public function save(int $orderId, string $recordId, PaymentRecord $record): void
    {
        $key = $this->key($recordId);
        $order = $this->order($orderId);
        $data = $record->toArray();
        if ($order->meta_exists($key) && $order->get_meta($key, true, 'edit') === $data) {
            return;
        }
        $order->update_meta_data($key, $data);
        $order->save_meta_data();
    }

    private function key(string $recordId): string
    {
        if (preg_match('/\A[a-zA-Z0-9_-]{1,64}\z/D', $recordId) !== 1) {
            throw new InvalidArgumentException('Invalid payment record identifier.');
        }
        return '_jp_payment_record_' . $recordId;
    }

    private function order(int $orderId): \WC_Order
    {
        if ($orderId <= 0 || ! function_exists('wc_get_order')) {
            throw new InvalidArgumentException('An existing WooCommerce order is required.');
        }
        $order = wc_get_order($orderId);
        if (! $order instanceof \WC_Order || $order instanceof \WC_Order_Refund) {
            throw new InvalidArgumentException('An existing WooCommerce order is required.');
        }
        return $order;
    }
}
