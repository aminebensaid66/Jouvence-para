<?php

declare(strict_types=1);

namespace JouvencePara\Core\Analytics {
    if (! defined('JOUVENCE_PARA_CORE_FILE')) {
        define('JOUVENCE_PARA_CORE_FILE', __FILE__);
    }
    function wc_get_order(int $id): mixed { return $GLOBALS['jp_analytics_orders'][$id] ?? false; }
    function wp_salt(string $scheme = 'auth'): string { return 'analytics-fixture-salt-' . $scheme; }
    function sanitize_text_field(string $value): string { return trim(strip_tags($value)); }
    function WC(): object { return $GLOBALS['jp_analytics_wc']; }
    function wp_enqueue_script(string $handle, string $source, array $dependencies, string|false|null $version, bool $inFooter): void
    {
        $GLOBALS['jp_analytics_enqueued_script'] = [$handle, $source, $version, $dependencies, $inFooter];
    }
    function plugins_url(string $path, string $pluginFile): string
    {
        unset($pluginFile);
        return '/plugins/' . $path;
    }
}

namespace {
    function wp_enqueue_script(string $handle, string $source, array $dependencies, string|false|null $version, bool $inFooter): void
    {
        $GLOBALS['jp_analytics_enqueued_script'] = [$handle, $source, $version, $dependencies, $inFooter];
    }

    function plugins_url(string $path, string $pluginFile): string
    {
        unset($pluginFile);
        return '/plugins/' . $path;
    }
}
