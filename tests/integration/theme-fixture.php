<?php

declare(strict_types=1);

/** Capture theme registrations once, independently of each module's hook registry. */
function jp_test_theme_hooks(): array
{
    static $themeHooks = null;

    if ($themeHooks !== null) {
        return $themeHooks;
    }

    if (! defined('ABSPATH')) {
        define('ABSPATH', __DIR__ . '/');
    }

    $previousHooks = $GLOBALS['jp_test_hooks'] ?? [];
    $GLOBALS['jp_test_hooks'] = [];
    try {
        require_once __DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/functions.php';
        $themeHooks = $GLOBALS['jp_test_hooks'];
    } finally {
        $GLOBALS['jp_test_hooks'] = $previousHooks;
    }

    return $themeHooks;
}
