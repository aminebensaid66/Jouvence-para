<?php

declare(strict_types=1);

namespace JouvencePara\Core\Infrastructure\Migrations;

use InvalidArgumentException;
use RuntimeException;

final class Migrator
{
    public const SCHEMA_VERSION_OPTION = 'jp_core_schema_version';
    public const MIGRATION_LOCK_OPTION = 'jp_core_migration_lock';
    private const LOCK_TTL_SECONDS = 300;

    /** @var array<int, Migration> */
    private array $migrations = [];

    /** @param iterable<Migration> $migrations */
    public function __construct(iterable $migrations)
    {
        foreach ($migrations as $migration) {
            $version = $migration->version();
            if ($version < 1) {
                throw new InvalidArgumentException('Migration versions must be positive integers.');
            }
            if (isset($this->migrations[$version])) {
                throw new InvalidArgumentException("Duplicate migration version: $version");
            }
            $this->migrations[$version] = $migration;
        }

        ksort($this->migrations, SORT_NUMERIC);
    }

    public function migrate(): void
    {
        $currentVersion = (int) get_option(self::SCHEMA_VERSION_OPTION, '0');
        if (! $this->hasPendingMigration($currentVersion) || ! $this->acquireLock()) {
            return;
        }

        try {
            $currentVersion = (int) get_option(self::SCHEMA_VERSION_OPTION, '0');

            foreach ($this->migrations as $version => $migration) {
                if ($version <= $currentVersion) {
                    continue;
                }

                $migration->up();
                if (! update_option(self::SCHEMA_VERSION_OPTION, (string) $version, false)) {
                    throw new RuntimeException("Unable to persist schema version $version.");
                }
                $currentVersion = $version;
            }
        } finally {
            delete_option(self::MIGRATION_LOCK_OPTION);
        }
    }

    private function hasPendingMigration(int $currentVersion): bool
    {
        $latestVersion = $this->migrations === [] ? 0 : max(array_keys($this->migrations));

        return $latestVersion > $currentVersion;
    }

    private function acquireLock(): bool
    {
        $now = time();
        if (add_option(self::MIGRATION_LOCK_OPTION, (string) $now, '', false)) {
            return true;
        }

        $lockedAt = (int) get_option(self::MIGRATION_LOCK_OPTION, '0');
        if ($lockedAt <= 0 || ($now - $lockedAt) <= self::LOCK_TTL_SECONDS) {
            return false;
        }

        delete_option(self::MIGRATION_LOCK_OPTION);

        return add_option(self::MIGRATION_LOCK_OPTION, (string) $now, '', false);
    }
}
