<?php

declare(strict_types=1);

namespace JouvencePara\Core\Observability;

final class Logger
{
    /** @param array<string, mixed> $context */
    public function error(string $event, array $context = []): void
    {
        $payload = [
            'service' => 'jouvence-para',
            'level' => 'error',
            'event' => sanitize_key($event),
            'environment' => wp_get_environment_type(),
            'release' => defined('JOUVENCE_PARA_CORE_VERSION') ? JOUVENCE_PARA_CORE_VERSION : 'unknown',
            'timestamp' => gmdate('c'),
            'context' => Redactor::context($context),
        ];

        $encoded = wp_json_encode($payload, JSON_UNESCAPED_SLASHES);
        if (is_string($encoded)) {
            error_log($encoded);
        }
    }
}
