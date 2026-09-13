<?php

declare(strict_types=1);

use JouvencePara\Core\Infrastructure\Migrations\Migration;
use JouvencePara\Core\Infrastructure\Migrations\Migrator;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Infrastructure/Migrations/Migration.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Infrastructure/Migrations/Migrator.php';

return [
    'applies migrations in version order and records progress' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_options'] = [];
        $GLOBALS['jp_test_failed_update'] = null;
        $applied = [];

        $versionTwo = new class($applied) implements Migration {
            /** @var list<int> */
            private array $applied;
            public function __construct(array &$applied) { $this->applied = &$applied; }
            public function version(): int { return 2; }
            public function up(): void { $this->applied[] = 2; }
        };
        $versionOne = new class($applied) implements Migration {
            /** @var list<int> */
            private array $applied;
            public function __construct(array &$applied) { $this->applied = &$applied; }
            public function version(): int { return 1; }
            public function up(): void { $this->applied[] = 1; }
        };

        $migrator = new Migrator([$versionTwo, $versionOne]);
        $migrator->migrate();

        $test->assertSame([1, 2], $applied);
        $test->assertSame('2', $GLOBALS['jp_test_options'][Migrator::SCHEMA_VERSION_OPTION]);

        $migrator->migrate();
        $test->assertSame([1, 2], $applied, 'migrations must not rerun after their version is recorded');
    },
    'does not migrate while another request owns the lock' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_options'] = [Migrator::MIGRATION_LOCK_OPTION => (string) time()];
        $applied = false;
        $migration = new class($applied) implements Migration {
            private bool $applied;
            public function __construct(bool &$applied) { $this->applied = &$applied; }
            public function version(): int { return 1; }
            public function up(): void { $this->applied = true; }
        };

        (new Migrator([$migration]))->migrate();

        $test->assertSame(false, $applied);
        $test->assertTrue(isset($GLOBALS['jp_test_options'][Migrator::MIGRATION_LOCK_OPTION]));
    },
    'releases the lock when a migration fails' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_options'] = [];
        $migration = new class implements Migration {
            public function version(): int { return 1; }
            public function up(): void { throw new RuntimeException('migration failed'); }
        };

        $thrown = false;
        try {
            (new Migrator([$migration]))->migrate();
        } catch (RuntimeException $exception) {
            $thrown = $exception->getMessage() === 'migration failed';
        }

        $test->assertTrue($thrown, 'migration exception must be propagated');
        $test->assertTrue(! isset($GLOBALS['jp_test_options'][Migrator::MIGRATION_LOCK_OPTION]));
    },
    'fails safely when the schema version cannot be persisted' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_options'] = [];
        $GLOBALS['jp_test_failed_update'] = Migrator::SCHEMA_VERSION_OPTION;
        $migration = new class implements Migration {
            public function version(): int { return 1; }
            public function up(): void {}
        };

        $thrown = false;
        try {
            (new Migrator([$migration]))->migrate();
        } catch (RuntimeException $exception) {
            $thrown = str_contains($exception->getMessage(), 'persist schema version');
        } finally {
            $GLOBALS['jp_test_failed_update'] = null;
        }

        $test->assertTrue($thrown, 'schema-version persistence failure must be visible');
        $test->assertTrue(! isset($GLOBALS['jp_test_options'][Migrator::MIGRATION_LOCK_OPTION]));
    },
];
