<?php

declare(strict_types=1);

namespace JouvencePara\Core\Bootstrap;

use JouvencePara\Core\Contracts\Module;

final class Plugin
{
    /** @var list<Module> */
    private array $modules;

    public function __construct(?array $modules = null)
    {
        $this->modules = $modules ?? [];
    }

    public function boot(): void
    {
        foreach ($this->modules as $module) {
            $module->register();
        }
    }
}
