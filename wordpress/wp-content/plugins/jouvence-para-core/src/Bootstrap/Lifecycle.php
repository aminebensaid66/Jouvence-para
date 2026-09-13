<?php

declare(strict_types=1);

namespace JouvencePara\Core\Bootstrap;

final class Lifecycle
{
    private const PLUGIN_VERSION_OPTION = 'jp_core_plugin_version';
    private const SCHEMA_VERSION_OPTION = 'jp_core_schema_version';

    public static function activate(): void
    {
        update_option(self::PLUGIN_VERSION_OPTION, JOUVENCE_PARA_CORE_VERSION, false);

        if (get_option(self::SCHEMA_VERSION_OPTION, null) === null) {
            add_option(self::SCHEMA_VERSION_OPTION, JOUVENCE_PARA_CORE_SCHEMA_VERSION, '', false);
        }
    }

    public static function deactivate(): void
    {
        // Deactivation intentionally preserves commerce/configuration data.
    }
}
