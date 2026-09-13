<?php

declare(strict_types=1);

namespace JouvencePara\Core\Infrastructure\Migrations;

interface Migration
{
    public function version(): int;

    public function up(): void;
}
