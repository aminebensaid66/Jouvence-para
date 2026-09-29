<?php

declare(strict_types=1);

namespace JouvencePara\Core\Jobs;

interface Scheduler
{
    public function enqueue(string $hook, array $args, string $group): int;

    public function schedule(int $timestamp, string $hook, array $args, string $group): int;

    /**
     * @return list<array{id: int, hook: string, args: array<mixed>}>
     */
    public function failedActions(string $group, int $limit, int $offset = 0): array;
}
