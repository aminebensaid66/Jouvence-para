<?php

declare(strict_types=1);

namespace JouvencePara\Core\Webhooks;

interface EventStore
{
    /** @return 'claimed'|'succeeded'|'permanent'|'busy'|'conflict'|'unavailable' */
    public function claim(string $key, string $provider, string $type, string $payloadHash, string $owner): string;

    /** Persist a fixed result code without payloads, credentials or exception messages. */
    public function finish(string $key, string $state, string $owner): bool;
}
