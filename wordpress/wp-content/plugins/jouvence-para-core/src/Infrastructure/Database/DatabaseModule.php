<?php

declare(strict_types=1);

namespace JouvencePara\Core\Infrastructure\Database;

use JouvencePara\Core\Contracts\Module;
use JouvencePara\Core\Infrastructure\Migrations\BaselineMigration;
use JouvencePara\Core\Infrastructure\Migrations\Migrator;

final class DatabaseModule implements Module
{
    public function register(): void
    {
        (new Migrator([new BaselineMigration()]))->migrate();
    }
}
