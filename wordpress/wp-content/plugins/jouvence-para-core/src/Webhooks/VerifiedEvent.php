<?php

declare(strict_types=1);

namespace JouvencePara\Core\Webhooks;

use InvalidArgumentException;

final class VerifiedEvent
{
    /** @param array<string, mixed> $payload Verified payload, held in memory only. */
    public function __construct(public readonly string $id, public readonly string $type, public readonly array $payload = [])
    {
        if ($id === '' || strlen($id) > 191 || preg_match('/[\x00-\x1f\x7f]/', $id) === 1
            || preg_match('/^[a-zA-Z0-9_.:-]{1,80}$/D', $type) !== 1) {
            throw new InvalidArgumentException('Invalid verified webhook identifiers.');
        }
    }
}
