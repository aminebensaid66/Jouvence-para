<?php

declare(strict_types=1);

namespace {
    function get_user_by(string $field, string $value): mixed
    {
        if ($field !== 'email') {
            return false;
        }
        foreach ($GLOBALS['jp_privacy_users'] ?? [] as $user) {
            if (strcasecmp($user->user_email, $value) === 0) {
                return $user;
            }
        }
        return false;
    }

    function get_user_meta(int $userId, string $key, bool $single = false): mixed
    {
        unset($single);
        return $GLOBALS['jp_privacy_meta'][$userId][$key] ?? '';
    }

    function metadata_exists(string $type, int $userId, string $key): bool
    {
        return $type === 'user' && array_key_exists($key, $GLOBALS['jp_privacy_meta'][$userId] ?? []);
    }

    function delete_user_meta(int $userId, string $key): bool
    {
        if (($GLOBALS['jp_privacy_delete_failure'] ?? null) === $key) {
            return false;
        }
        unset($GLOBALS['jp_privacy_meta'][$userId][$key]);
        return true;
    }
}

namespace JouvencePara\Core\Privacy {
    function add_filter(string $hook, mixed $callback): void
    {
        $GLOBALS['jp_privacy_hooks'][$hook][] = $callback;
    }

    function __(string $text, string $domain = ''): string
    {
        unset($domain);
        return $text;
    }

    function sanitize_email(string $email): string
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL) ?: '';
    }
}
