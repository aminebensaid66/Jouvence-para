<?php

declare(strict_types=1);

namespace JouvencePara\Core\I18n;

use JouvencePara\Core\Contracts\Module;

final class I18nModule implements Module
{
    public function register(): void
    {
        add_action('init', [$this, 'loadTextDomain'], 1);
    }

    public function loadTextDomain(): void
    {
        load_plugin_textdomain(
            'jouvence-para-core',
            false,
            dirname(plugin_basename(JOUVENCE_PARA_CORE_FILE)) . '/languages'
        );
    }
}
