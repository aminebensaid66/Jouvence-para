<?php

declare(strict_types=1);

namespace JouvencePara\Core\Payments;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

/** A provider-neutral snapshot; status vocabularies belong to approved adapters. */
final class PaymentRecord
{
    private const FIELDS = ['method', 'status', 'provider', 'transaction_id', 'paid_at', 'refund_status'];

    private array $data;

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function fromArray(array $data): self
    {
        if (array_diff(array_keys($data), self::FIELDS) !== [] || array_diff(self::FIELDS, array_keys($data)) !== []) {
            throw new InvalidArgumentException('Invalid payment record fields.');
        }

        foreach (['method', 'status', 'refund_status'] as $field) {
            self::validateCode($data[$field]);
        }
        if ($data['provider'] !== null) {
            self::validateCode($data['provider']);
        }
        if ($data['transaction_id'] !== null) {
            if (! is_string($data['transaction_id']) || preg_match('/\A[A-Za-z0-9._:\/-]{1,255}\z/D', $data['transaction_id']) !== 1) {
                throw new InvalidArgumentException('Invalid provider transaction reference.');
            }
            if ($data['provider'] === null) {
                throw new InvalidArgumentException('A transaction reference requires a provider.');
            }
        }
        if ($data['paid_at'] !== null) {
            if (! is_string($data['paid_at'])) {
                throw new InvalidArgumentException('Invalid payment timestamp.');
            }
            $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:s\Z', $data['paid_at'], new DateTimeZone('UTC'));
            if ($date === false || $date->format('Y-m-d\TH:i:s\Z') !== $data['paid_at']) {
                throw new InvalidArgumentException('Payment timestamp must be a valid UTC instant.');
            }
        }

        // Canonical order makes replay comparison deterministic.
        $canonical = [];
        foreach (self::FIELDS as $field) {
            $canonical[$field] = $data[$field];
        }
        return new self($canonical);
    }

    public function toArray(): array
    {
        return $this->data;
    }

    /** Never log toArray(): even opaque provider references are sensitive. */
    public function logContext(): array
    {
        return array_intersect_key($this->data, array_flip(['method', 'status', 'provider', 'refund_status']));
    }

    private static function validateCode(mixed $value): void
    {
        if (! is_string($value) || preg_match('/\A[a-z][a-z0-9_-]{0,63}\z/D', $value) !== 1) {
            throw new InvalidArgumentException('Invalid payment code.');
        }
    }
}
