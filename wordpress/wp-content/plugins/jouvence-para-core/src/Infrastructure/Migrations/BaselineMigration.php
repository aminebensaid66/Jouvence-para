<?php

declare(strict_types=1);

namespace JouvencePara\Core\Infrastructure\Migrations;

final class BaselineMigration implements Migration
{
    public function version(): int
    {
        return 1;
    }

    public function up(): void
    {
        // Version 1 establishes the migration baseline; it creates no custom tables.
    }
}
