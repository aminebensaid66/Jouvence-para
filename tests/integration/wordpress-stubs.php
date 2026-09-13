<?php

declare(strict_types=1);

$GLOBALS['jp_test_options'] = [];

function update_option(string $name, mixed $value, bool $autoload = true): bool
{
    if (($GLOBALS['jp_test_failed_update'] ?? null) === $name) {
        return false;
    }

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

function delete_option(string $name): bool
{
    if (! array_key_exists($name, $GLOBALS['jp_test_options'])) {
        return false;
    }

    unset($GLOBALS['jp_test_options'][$name]);
    return true;
}
