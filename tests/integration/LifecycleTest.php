<?php

declare(strict_types=1);

use JouvencePara\Core\Bootstrap\Lifecycle;

const JOUVENCE_PARA_CORE_VERSION = '0.1.0-test';
const JOUVENCE_PARA_CORE_SCHEMA_VERSION = '0';

$GLOBALS['jp_test_options'] = [];

function update_option(string $name, mixed $value, bool $autoload = true): bool
{
    $GLOBALS['jp_test_options'][$name] = $value;
    return true;
}

function get_option(string $name, mixed $default = false): mixed
{
    return $GLOBALS['jp_test_options'][$name] ?? $default;
}

function add_option(string $name, mixed $value, string $deprecated = '', bool $autoload = true): bool
{
    if (array_key_exists($name, $GLOBALS['jp_test_options'])) {
        return false;
    }

    $GLOBALS['jp_test_options'][$name] = $value;
    return true;
}

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Bootstrap/Lifecycle.php';

return [
    'activation records plugin and initial schema versions' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_options'] = [];
        Lifecycle::activate();

        $test->assertSame('0.1.0-test', $GLOBALS['jp_test_options']['jp_core_plugin_version']);
        $test->assertSame('0', $GLOBALS['jp_test_options']['jp_core_schema_version']);
    },
    'repeat activation preserves an existing schema version' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_options'] = ['jp_core_schema_version' => '99'];
        Lifecycle::activate();

        $test->assertSame('99', $GLOBALS['jp_test_options']['jp_core_schema_version']);
        $test->assertSame('0.1.0-test', $GLOBALS['jp_test_options']['jp_core_plugin_version']);
    },
];
