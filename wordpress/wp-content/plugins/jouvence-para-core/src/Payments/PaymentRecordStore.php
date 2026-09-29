<?php

declare(strict_types=1);

namespace JouvencePara\Core\Payments;

interface PaymentRecordStore
{
    public function find(int $orderId, string $recordId): ?PaymentRecord;

    /** Replay of an identical snapshot is a no-op; this does not authorize transitions. */
    public function save(int $orderId, string $recordId, PaymentRecord $record): void;
}
