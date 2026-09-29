<?php

declare(strict_types=1);

namespace JouvencePara\Core\Jobs;

use InvalidArgumentException;

final class RetryPolicy
{
    public function __construct(
        private readonly int $maxAttempts,
        private readonly int $initialDelaySeconds,
        private readonly int $maximumDelaySeconds,
        private readonly int $growthFactor
    ) {
        if ($maxAttempts < 1
            || $initialDelaySeconds < 1
            || $maximumDelaySeconds < $initialDelaySeconds
            || $growthFactor < 1
        ) {
            throw new InvalidArgumentException('The background job retry policy is invalid.');
        }
    }

    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /** Return null when the failed attempt has exhausted the policy. */
    public function delayAfterFailure(int $attempt): ?int
    {
        if ($attempt < 1 || $attempt >= $this->maxAttempts) {
            return null;
        }

        $delay = $this->initialDelaySeconds;
        for ($index = 1; $index < $attempt && $delay < $this->maximumDelaySeconds; $index++) {
            $delay = $delay > intdiv($this->maximumDelaySeconds, $this->growthFactor)
                ? $this->maximumDelaySeconds
                : min($this->maximumDelaySeconds, $delay * $this->growthFactor);
        }

        return $delay;
    }
}
