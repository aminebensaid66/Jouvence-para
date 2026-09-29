<?php

declare(strict_types=1);

namespace JouvencePara\Core\Jobs;

final class ActionSchedulerAdapter implements Scheduler
{
    public function enqueue(string $hook, array $args, string $group): int
    {
        if (! $this->isInitialized()) {
            return 0;
        }

        return (int) as_enqueue_async_action($hook, $args, $group, false);
    }

    public function schedule(int $timestamp, string $hook, array $args, string $group): int
    {
        if (! $this->isInitialized()) {
            return 0;
        }

        return (int) as_schedule_single_action($timestamp, $hook, $args, $group, false);
    }

    public function failedActions(string $group, int $limit, int $offset = 0): array
    {
        if (! $this->isInitialized()) {
            return [];
        }

        $actions = as_get_scheduled_actions(
            [
                'group' => $group,
                'status' => 'failed',
                'per_page' => max(1, min(100, $limit)),
                'offset' => max(0, $offset),
                'orderby' => 'date',
                'order' => 'DESC',
            ],
            'OBJECT'
        );

        if (! is_array($actions)) {
            return [];
        }

        $failed = [];
        foreach ($actions as $actionId => $action) {
            if (! is_object($action)
                || ! method_exists($action, 'get_hook')
                || ! method_exists($action, 'get_args')
                || ! method_exists($action, 'get_group')
                || $action->get_group() !== $group
            ) {
                continue;
            }

            $args = $action->get_args();
            if (! is_array($args)) {
                continue;
            }

            $failed[] = [
                'id' => (int) $actionId,
                'hook' => (string) $action->get_hook(),
                'args' => $args,
            ];
        }

        return $failed;
    }

    private function isInitialized(): bool
    {
        return function_exists('as_enqueue_async_action')
            && function_exists('as_schedule_single_action')
            && function_exists('as_get_scheduled_actions')
            && function_exists('did_action')
            && did_action('action_scheduler_init') > 0;
    }
}
