<?php

declare(strict_types=1);

use JouvencePara\Core\Jobs\JobContext;
use JouvencePara\Core\Jobs\JobHandler;
use JouvencePara\Core\Jobs\JobQueue;
use JouvencePara\Core\Jobs\JobsModule;
use JouvencePara\Core\Jobs\RetryPolicy;
use JouvencePara\Core\Jobs\Scheduler;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Jobs/Scheduler.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Jobs/ActionSchedulerAdapter.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Jobs/RetryPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Jobs/JobContext.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Jobs/JobHandler.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Jobs/JobQueue.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Jobs/JobsModule.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Observability/Redactor.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Observability/Logger.php';

final class TestJobScheduler implements Scheduler
{
    /** @var list<array{hook: string, args: array<mixed>, group: string}> */
    public array $enqueued = [];

    /** @var list<array{timestamp: int, hook: string, args: array<mixed>, group: string}> */
    public array $scheduled = [];

    /** @var list<array{id: int, hook: string, args: array<mixed>}> */
    public array $failed = [];

    public function enqueue(string $hook, array $args, string $group): int
    {
        $this->enqueued[] = ['hook' => $hook, 'args' => $args, 'group' => $group];
        return 100 + count($this->enqueued);
    }

    public function schedule(int $timestamp, string $hook, array $args, string $group): int
    {
        $this->scheduled[] = ['timestamp' => $timestamp, 'hook' => $hook, 'args' => $args, 'group' => $group];
        return 200 + count($this->scheduled);
    }

    public function failedActions(string $group, int $limit, int $offset = 0): array
    {
        return array_slice($this->failed, $offset, $limit);
    }
}

final class TestJobHandler implements JobHandler
{
    /** @var list<array{payload: array<string|int, mixed>, context: JobContext}> */
    public array $calls = [];

    public function __construct(private readonly bool $fails = false)
    {
    }

    public function handle(array $payload, JobContext $context): void
    {
        $this->calls[] = ['payload' => $payload, 'context' => $context];
        if ($this->fails) {
            throw new RuntimeException('Private failure for person@example.com');
        }
    }
}

final class TestScheduledAction
{
    public function __construct(
        private readonly string $hook,
        private readonly array $args,
        private readonly string $group
    ) {
    }

    public function get_hook(): string { return $this->hook; }
    public function get_args(): array { return $this->args; }
    public function get_group(): string { return $this->group; }
}

if (! function_exists('did_action')) {
    function did_action(string $hook): int
    {
        return $GLOBALS['jp_test_action_scheduler_initialized'] ?? 0;
    }
}
if (! function_exists('as_enqueue_async_action')) {
    function as_enqueue_async_action(string $hook, array $args, string $group, bool $unique = false): int
    {
        $GLOBALS['jp_test_as_calls'][] = ['type' => 'enqueue', 'hook' => $hook, 'args' => $args, 'group' => $group, 'unique' => $unique];
        return 301;
    }
}
if (! function_exists('as_schedule_single_action')) {
    function as_schedule_single_action(int $timestamp, string $hook, array $args, string $group, bool $unique = false): int
    {
        $GLOBALS['jp_test_as_calls'][] = ['type' => 'schedule', 'timestamp' => $timestamp, 'hook' => $hook, 'args' => $args, 'group' => $group, 'unique' => $unique];
        return 302;
    }
}
if (! function_exists('as_get_scheduled_actions')) {
    function as_get_scheduled_actions(array $args, string $format = 'OBJECT'): array
    {
        $GLOBALS['jp_test_as_query'] = ['args' => $args, 'format' => $format];
        return $GLOBALS['jp_test_as_actions'] ?? [];
    }
}
if (! function_exists('add_management_page')) {
    function add_management_page(string $pageTitle, string $menuTitle, string $capability, string $menuSlug, mixed $callback): string
    {
        $GLOBALS['jp_test_admin_pages'][] = [
            'page_title' => $pageTitle,
            'menu_title' => $menuTitle,
            'capability' => $capability,
            'menu_slug' => $menuSlug,
            'callback' => $callback,
        ];
        return $menuSlug;
    }
}

return [
    'requires idempotency keys for duplicate-sensitive job registration' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        $scheduler = new TestJobScheduler();
        $handler = new TestJobHandler();
        $queue = new JobQueue($scheduler);
        $queue->register('catalog_sync', $handler, new RetryPolicy(5, 60, 3600, 2), true);

        $test->assertSame(false, $queue->enqueue('catalog_sync', ['product_id' => 42]));
        $test->assertSame(101, $queue->enqueue('catalog_sync', ['product_id' => 42], 'catalog-42'));
        $test->assertSame('jouvence-para', $scheduler->enqueued[0]['group']);
        $test->assertSame(1, $scheduler->enqueued[0]['args'][2]);
    },
    'retries failures with the same idempotency key and safe log context' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        $scheduler = new TestJobScheduler();
        $handler = new TestJobHandler(true);
        $logged = [];
        $queue = new JobQueue(
            $scheduler,
            static function (string $event, array $context) use (&$logged): void {
                $logged[] = ['event' => $event, 'context' => $context];
            }
        );
        $queue->register('email_retry', $handler, new RetryPolicy(3, 60, 3600, 2), true);
        $callback = $GLOBALS['jp_test_hooks']['action']['jouvence_para_job_email_retry'][0][0];
        $before = time();

        $callback(['notification_id' => 7], 'notification-7', 1);

        $test->assertSame(1, count($scheduler->scheduled));
        $test->assertSame(['notification_id' => 7], $scheduler->scheduled[0]['args'][0]);
        $test->assertSame('notification-7', $scheduler->scheduled[0]['args'][1]);
        $test->assertSame(2, $scheduler->scheduled[0]['args'][2]);
        $test->assertTrue($scheduler->scheduled[0]['timestamp'] >= $before + 60);
        $test->assertSame('background_job_failed', $logged[0]['event']);
        $test->assertSame('RuntimeException', $logged[0]['context']['exception_class']);
        $test->assertTrue(! isset($logged[0]['context']['payload']));
        $test->assertTrue(! isset($logged[0]['context']['idempotency_key']));
    },
    'masks terminal exceptions so their message is not persisted by Action Scheduler' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        $scheduler = new TestJobScheduler();
        $queue = new JobQueue($scheduler, static function (): void {});
        $queue->register('index_product', new TestJobHandler(true), new RetryPolicy(1, 60, 3600, 2));
        $callback = $GLOBALS['jp_test_hooks']['action']['jouvence_para_job_index_product'][0][0];

        try {
            $callback(['product_id' => 9], null, 1);
        } catch (RuntimeException $error) {
            $test->assertSame('A background job failed. Review the Jouvence Para job log.', $error->getMessage());
            $test->assertSame(null, $error->getPrevious());
            return;
        }

        $test->assertTrue(false, 'Expected the final failure to be visible to Action Scheduler.');
    },
    'recovery starts a fresh retry cycle for a verified failed Jouvence Para action' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        $scheduler = new TestJobScheduler();
        $scheduler->failed[] = [
            'id' => 84,
            'hook' => 'jouvence_para_job_catalog_sync',
            'args' => [['product_id' => 42], 'catalog-42', 5],
        ];
        $queue = new JobQueue($scheduler);
        $queue->register('catalog_sync', new TestJobHandler(), new RetryPolicy(5, 60, 3600, 2), true);

        $test->assertTrue($queue->retryFailed(84));
        $test->assertSame(1, $scheduler->enqueued[0]['args'][2]);
        $test->assertSame('catalog-42', $scheduler->enqueued[0]['args'][1]);
        $test->assertSame(false, $queue->retryFailed(85));
    },
    'rejects object payloads before queue persistence' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        $scheduler = new TestJobScheduler();
        $queue = new JobQueue($scheduler);
        $queue->register('cleanup', new TestJobHandler(), new RetryPolicy(1, 60, 60, 1));

        $test->assertSame(false, $queue->enqueue('cleanup', ['object' => new stdClass()]));
        $test->assertSame([], $scheduler->enqueued);
    },
    'Action Scheduler adapter waits for initialization and maps failed actions by group' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_action_scheduler_initialized'] = 0;
        $GLOBALS['jp_test_as_calls'] = [];
        $GLOBALS['jp_test_as_actions'] = [
            18 => new TestScheduledAction('jouvence_para_job_catalog_sync', [['product_id' => 42], 'catalog-42', 5], 'jouvence-para'),
            19 => new TestScheduledAction('other_job', [], 'another-plugin'),
        ];
        $adapter = new \JouvencePara\Core\Jobs\ActionSchedulerAdapter();

        $test->assertSame(0, $adapter->enqueue('hook', [], JobQueue::GROUP));
        $GLOBALS['jp_test_action_scheduler_initialized'] = 1;
        $test->assertSame(301, $adapter->enqueue('hook', ['id' => 1], JobQueue::GROUP));
        $test->assertSame(302, $adapter->schedule(1234, 'hook', ['id' => 1], JobQueue::GROUP));
        $failed = $adapter->failedActions(JobQueue::GROUP, 50);

        $test->assertSame(1, count($failed));
        $test->assertSame(18, $failed[0]['id']);
        $test->assertSame('failed', $GLOBALS['jp_test_as_query']['args']['status']);
        $test->assertSame(false, $GLOBALS['jp_test_as_calls'][0]['unique']);
    },
    'registers an admin page and its recovery endpoint' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        $module = new JobsModule(new JobQueue(new TestJobScheduler()));
        $module->register();
        $module->registerAdminPage();

        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['admin_menu']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['admin_post_jp_retry_failed_job']));
        $test->assertSame('manage_woocommerce', $GLOBALS['jp_test_admin_pages'][0]['capability']);
    },
];
