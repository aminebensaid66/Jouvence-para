<?php

declare(strict_types=1);

use JouvencePara\Core\Bootstrap\Plugin;
use JouvencePara\Core\Contracts\Module;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Contracts/Module.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Bootstrap/ModuleRegistry.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Bootstrap/Plugin.php';

return [
    'boots injected modules without a theme' => static function (TestHarness $test): void {
        $module = new class implements Module {
            public bool $registered = false;

            public function register(): void
            {
                $this->registered = true;
            }
        };

        $plugin = new Plugin([$module]);
        $plugin->boot();

        $test->assertTrue($module->registered);
    },
    'allows modules to be registered before boot' => static function (TestHarness $test): void {
        $module = new class implements Module {
            public int $registrations = 0;

            public function register(): void
            {
                $this->registrations++;
            }
        };

        $plugin = new Plugin();
        $plugin->addModule($module);
        $plugin->boot();

        $test->assertSame(1, $module->registrations);
    },
];
