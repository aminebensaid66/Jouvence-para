<?php

declare(strict_types=1);

namespace JouvencePara\Core\Bootstrap;

use JouvencePara\Core\Contracts\Module;

final class Plugin
{
    private ModuleRegistry $modules;

    /** @param iterable<Module> $modules */
    public function __construct(iterable $modules = [])
    {
        $this->modules = new ModuleRegistry($modules);
    }

    public function addModule(Module $module): void
    {
        $this->modules->add($module);
    }

    public function boot(): void
    {
        $this->modules->registerAll();
    }
}
