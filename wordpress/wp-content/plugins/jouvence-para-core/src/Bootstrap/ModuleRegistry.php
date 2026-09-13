<?php

declare(strict_types=1);

namespace JouvencePara\Core\Bootstrap;

use JouvencePara\Core\Contracts\Module;

final class ModuleRegistry
{
    /** @var list<Module> */
    private array $modules = [];

    /** @param iterable<Module> $modules */
    public function __construct(iterable $modules = [])
    {
        foreach ($modules as $module) {
            $this->add($module);
        }
    }

    public function add(Module $module): void
    {
        $this->modules[] = $module;
    }

    /** @return list<Module> */
    public function all(): array
    {
        return $this->modules;
    }

    public function registerAll(): void
    {
        foreach ($this->modules as $module) {
            $module->register();
        }
    }
}
