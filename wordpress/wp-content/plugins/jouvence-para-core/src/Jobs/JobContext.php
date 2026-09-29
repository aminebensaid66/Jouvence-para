<?php

declare(strict_types=1);

namespace JouvencePara\Core\Jobs;

final class JobContext
{
    public function __construct(
        public readonly int $attempt,
        public readonly int $maxAttempts,
        public readonly ?string $idempotencyKey
    ) {
    }
}
