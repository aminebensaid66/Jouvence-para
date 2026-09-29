<?php

declare(strict_types=1);

namespace JouvencePara\Core\Jobs;

use Closure;
use JouvencePara\Core\Observability\Logger;
use RuntimeException;
use Throwable;

final class JobQueue
{
    public const GROUP = 'jouvence-para';

    private const HOOK_PREFIX = 'jouvence_para_job_';
    private const MAX_PAYLOAD_BYTES = 20000;
    private const MAX_FAILED_ACTIONS_TO_SEARCH = 10000;

    /** @var array<string, array{handler: JobHandler, requires_idempotency_key: bool, retry_policy: RetryPolicy}> */
    private array $handlers = [];

    private Closure $logError;

    public function __construct(
        private readonly Scheduler $scheduler = new ActionSchedulerAdapter(),
        ?callable $logError = null
    ) {
        $logger = new Logger();
        $this->logError = $logError === null
            ? static fn (string $event, array $context): mixed => $logger->error($event, $context)
            : Closure::fromCallable($logError);
    }

    public function register(
        string $name,
        JobHandler $handler,
        RetryPolicy $retryPolicy,
        bool $requiresIdempotencyKey = false,
    ): void {
        if (! $this->isValidName($name)) {
            throw new RuntimeException('The background job name is invalid.');
        }

        if (isset($this->handlers[$name])) {
            throw new RuntimeException('The background job is already registered.');
        }

        $this->handlers[$name] = [
            'handler' => $handler,
            'requires_idempotency_key' => $requiresIdempotencyKey,
            'retry_policy' => $retryPolicy,
        ];

        add_action(
            $this->hookFor($name),
            function (mixed $payload = null, mixed $idempotencyKey = null, mixed $attempt = 1) use ($name): void {
                $this->run($name, $payload, $idempotencyKey, $attempt);
            },
            10,
            3
        );
    }

    /** @param array<string|int, mixed> $payload */
    public function enqueue(string $name, array $payload, ?string $idempotencyKey = null): int|false
    {
        $definition = $this->handlers[$name] ?? null;
        if ($definition === null
            || ! $this->isValidPayload($payload)
            || ! $this->isValidIdempotencyKey($idempotencyKey, $definition['requires_idempotency_key'])
        ) {
            return false;
        }

        try {
            $actionId = $this->scheduler->enqueue(
                $this->hookFor($name),
                [$payload, $idempotencyKey, 1],
                self::GROUP
            );
        } catch (Throwable $error) {
            ($this->logError)(
                'background_job_enqueue_failed',
                [
                    'job' => $name,
                    'idempotency_key_hash' => $this->keyHash($idempotencyKey),
                    'exception_class' => get_class($error),
                ]
            );

            return false;
        }

        if ($actionId < 1) {
            ($this->logError)(
                'background_job_enqueue_failed',
                ['job' => $name, 'idempotency_key_hash' => $this->keyHash($idempotencyKey)]
            );

            return false;
        }

        return $actionId;
    }

    /** @return list<array{id: int, job: string, attempt: int, recoverable: bool}> */
    public function failedJobs(int $limit = 50): array
    {
        $failedJobs = [];
        try {
            $actions = $this->scheduler->failedActions(self::GROUP, max(1, min(100, $limit)));
        } catch (Throwable $error) {
            ($this->logError)(
                'background_job_failure_query_failed',
                ['exception_class' => get_class($error)]
            );

            return [];
        }

        foreach ($actions as $action) {
            $name = $this->nameFromHook($action['hook']);
            if ($name === null) {
                continue;
            }

            $args = $action['args'];
            $definition = $this->handlers[$name] ?? null;
            $payload = $args[0] ?? null;
            $key = $args[1] ?? null;
            $attempt = $args[2] ?? null;
            $recoverable = $definition !== null
                && is_array($payload)
                && $this->isValidPayload($payload)
                && (is_string($key) || $key === null)
                && $this->isValidIdempotencyKey($key, $definition['requires_idempotency_key']);

            $failedJobs[] = [
                'id' => $action['id'],
                'job' => $name,
                'attempt' => is_int($attempt) ? $attempt : 0,
                'recoverable' => $recoverable,
            ];
        }

        return $failedJobs;
    }

    public function retryFailed(int $actionId): bool
    {
        if ($actionId < 1) {
            return false;
        }

        $action = $this->findFailedAction($actionId);
        if ($action === null) {
            return false;
        }

        $name = $this->nameFromHook($action['hook']);
        $definition = $name === null ? null : ($this->handlers[$name] ?? null);
        $args = $action['args'];
        $payload = $args[0] ?? null;
        $key = $args[1] ?? null;

        if ($name === null
            || $definition === null
            || ! is_array($payload)
            || ! $this->isValidPayload($payload)
            || (! is_string($key) && $key !== null)
            || ! $this->isValidIdempotencyKey($key, $definition['requires_idempotency_key'])
        ) {
            return false;
        }

        try {
            $newActionId = $this->scheduler->enqueue(
                $this->hookFor($name),
                [$payload, $key, 1],
                self::GROUP
            );
        } catch (Throwable $error) {
            ($this->logError)(
                'background_job_recovery_enqueue_failed',
                [
                    'job' => $name,
                    'idempotency_key_hash' => $this->keyHash($key),
                    'exception_class' => get_class($error),
                ]
            );

            return false;
        }

        if ($newActionId < 1) {
            ($this->logError)(
                'background_job_recovery_enqueue_failed',
                ['job' => $name, 'idempotency_key_hash' => $this->keyHash($key)]
            );

            return false;
        }

        return true;
    }

    private function run(string $name, mixed $payload, mixed $idempotencyKey, mixed $attempt): void
    {
        $definition = $this->handlers[$name] ?? null;
        if ($definition === null
            || ! is_array($payload)
            || ! $this->isValidPayload($payload)
            || (! is_string($idempotencyKey) && $idempotencyKey !== null)
            || ! $this->isValidIdempotencyKey($idempotencyKey, $definition['requires_idempotency_key'])
            || ! is_int($attempt)
            || $attempt < 1
            || $attempt > $definition['retry_policy']->maxAttempts()
        ) {
            ($this->logError)('background_job_invalid_action', ['job' => $name]);
            throw new RuntimeException('A background job action is invalid.');
        }

        $policy = $definition['retry_policy'];
        try {
            $definition['handler']->handle(
                $payload,
                new JobContext($attempt, $policy->maxAttempts(), $idempotencyKey)
            );
        } catch (Throwable $error) {
            $delay = $policy->delayAfterFailure($attempt);
            $retryScheduled = false;
            if ($delay !== null) {
                try {
                    $retryId = $this->scheduler->schedule(
                        time() + $delay,
                        $this->hookFor($name),
                        [$payload, $idempotencyKey, $attempt + 1],
                        self::GROUP
                    );
                    $retryScheduled = $retryId > 0;
                } catch (Throwable $scheduleError) {
                    ($this->logError)(
                        'background_job_retry_schedule_failed',
                        [
                            'job' => $name,
                            'attempt' => $attempt,
                            'exception_class' => get_class($scheduleError),
                        ]
                    );
                }
            }

            ($this->logError)(
                'background_job_failed',
                [
                    'job' => $name,
                    'attempt' => $attempt,
                    'max_attempts' => $policy->maxAttempts(),
                    'retry_scheduled' => $retryScheduled,
                    'retries_exhausted' => $delay === null,
                    'exception_class' => get_class($error),
                    'idempotency_key_hash' => $this->keyHash($idempotencyKey),
                ]
            );

            if ($retryScheduled) {
                return;
            }

            throw new RuntimeException('A background job failed. Review the Jouvence Para job log.');
        }
    }

    /** @return array{id: int, hook: string, args: array<mixed>}|null */
    private function findFailedAction(int $actionId): ?array
    {
        $pageSize = 100;
        try {
            for ($offset = 0; $offset < self::MAX_FAILED_ACTIONS_TO_SEARCH; $offset += $pageSize) {
                $actions = $this->scheduler->failedActions(self::GROUP, $pageSize, $offset);
                foreach ($actions as $action) {
                    if ($action['id'] === $actionId) {
                        return $action;
                    }
                }

                if (count($actions) < $pageSize) {
                    return null;
                }
            }
        } catch (Throwable $error) {
            ($this->logError)(
                'background_job_failure_query_failed',
                ['exception_class' => get_class($error)]
            );
        }

        return null;
    }

    /** @param array<string|int, mixed> $payload */
    private function isValidPayload(array $payload): bool
    {
        $encoded = json_encode($payload);
        return is_string($encoded)
            && strlen($encoded) <= self::MAX_PAYLOAD_BYTES
            && $this->containsOnlyScalarData($payload);
    }

    /** @param array<string|int, mixed> $data */
    private function containsOnlyScalarData(array $data, int $depth = 0): bool
    {
        if ($depth > 8) {
            return false;
        }

        foreach ($data as $value) {
            if (is_array($value)) {
                if (! $this->containsOnlyScalarData($value, $depth + 1)) {
                    return false;
                }
                continue;
            }

            if (! is_scalar($value) && $value !== null) {
                return false;
            }
        }

        return true;
    }

    private function isValidIdempotencyKey(?string $key, bool $required): bool
    {
        if ($key === null || $key === '') {
            return ! $required && ($key === null || $key === '');
        }

        return strlen($key) <= 191 && preg_match('/[\x00-\x1F\x7F]/', $key) !== 1;
    }

    private function isValidName(string $name): bool
    {
        return preg_match('/^[a-z][a-z0-9_]{0,63}$/', $name) === 1;
    }

    private function hookFor(string $name): string
    {
        return self::HOOK_PREFIX . $name;
    }

    private function nameFromHook(string $hook): ?string
    {
        if (! str_starts_with($hook, self::HOOK_PREFIX)) {
            return null;
        }

        $name = substr($hook, strlen(self::HOOK_PREFIX));
        return $this->isValidName($name) ? $name : null;
    }

    private function keyHash(?string $key): ?string
    {
        return $key === null || $key === '' ? null : hash('sha256', $key);
    }
}
