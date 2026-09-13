<?php

declare(strict_types=1);

namespace JouvencePara\Core\Privacy;

use JouvencePara\Core\Contracts\Module;

final class ConsentModule implements Module
{
    public function register(): void
    {
        add_filter('jouvence_para_consent_allows', [$this, 'allows'], 10, 2);
    }

    public function allows(bool $allowed, string $category): bool
    {
        unset($allowed);
        return ConsentPreferences::allows($category);
    }
}
