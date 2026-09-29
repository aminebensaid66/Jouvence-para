<?php

declare(strict_types=1);

namespace JouvencePara\Core\Jobs;

interface JobHandler
{
    /** @param array<string|int, mixed> $payload */
    public function handle(array $payload, JobContext $context): void;
}
