<?php

declare(strict_types=1);

namespace JouvencePara\Core\Security;

use JouvencePara\Core\Admin\RolePolicy;

final class SecurityPolicy
{
    /** @param list<string> $roles */
    public static function requiresTwoFactor(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($role === 'administrator' || $role === RolePolicy::STORE_MANAGER) {
                return true;
            }
        }
        return false;
    }

    public static function loginThrottleKey(string $username, string $ip): string
    {
        return 'jp_login_' . substr(hash('sha256', strtolower(trim($username)) . '|' . trim($ip)), 0, 40);
    }

    public static function twoFactorThrottleKey(int $userId): string
    {
        return 'jp_2fa_' . hash('sha256', (string) $userId);
    }
}
