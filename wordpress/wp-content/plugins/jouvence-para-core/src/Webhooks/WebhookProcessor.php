<?php

declare(strict_types=1);

namespace JouvencePara\Core\Webhooks;

use Throwable;

final class WebhookProcessor
{
    public function __construct(private readonly EventStore $store = new DatabaseEventStore())
    {
    }

    /** @param array<string, list<string>> $headers @return array{state: string, status: int} */
    public function handle(string $provider, WebhookAdapter $adapter, string $body, array $headers): array
    {
        if (preg_match('/^[a-z0-9][a-z0-9_-]{0,39}$/D', $provider) !== 1 || strlen($body) > 65536) {
            return ['state' => 'invalid_request', 'status' => 400];
        }
        try {
            $event = $adapter->verify($body, $headers);
        } catch (Throwable) {
            $event = null;
        }
        if (! $event instanceof VerifiedEvent) {
            return ['state' => 'invalid_signature', 'status' => 401];
        }
        $key = hash('sha256', $provider . "\0" . $event->id);
        try {
            $owner = bin2hex(random_bytes(16));
            $claim = $this->store->claim($key, $provider, $event->type, hash('sha256', $body), $owner);
        } catch (Throwable) {
            $claim = 'unavailable';
        }
        if ($claim !== 'claimed') {
            return match ($claim) {
                'succeeded' => ['state' => 'succeeded', 'status' => 200],
                'permanent' => ['state' => 'permanent', 'status' => 422],
                'conflict' => ['state' => 'conflict', 'status' => 409],
                default => ['state' => 'unavailable', 'status' => 503],
            };
        }
        try {
            $state = $adapter->process($event, $key);
        } catch (Throwable) {
            $state = 'uncertain';
        }
        if (! in_array($state, ['succeeded', 'retryable', 'permanent'], true)) {
            $state = 'uncertain';
        }
        try {
            $saved = $this->store->finish($key, $state, $owner);
        } catch (Throwable) {
            $saved = false;
        }
        if (! $saved) {
            return ['state' => 'unavailable', 'status' => 503];
        }
        return ['state' => $state, 'status' => match ($state) {
            'succeeded' => 200, 'permanent' => 422, default => 503,
        }];
    }
}
