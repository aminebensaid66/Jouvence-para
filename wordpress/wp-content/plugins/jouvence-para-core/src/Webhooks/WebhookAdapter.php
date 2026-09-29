<?php

declare(strict_types=1);

namespace JouvencePara\Core\Webhooks;

interface WebhookAdapter
{
    /** @param array<string, list<string>> $headers Verify raw-body signature and timestamp before returning an event. */
    public function verify(string $body, array $headers): ?VerifiedEvent;

    /**
     * Apply domain effects idempotently using this stable key. Return retryable only
     * when replay is safe; exceptions/unknown outcomes require reconciliation.
     * @return 'succeeded'|'retryable'|'permanent'
     */
    public function process(VerifiedEvent $event, string $idempotencyKey): string;
}
