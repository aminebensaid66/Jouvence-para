<?php

declare(strict_types=1);

namespace {
    final class JP_Test_Webhook_Database
    {
        public string $prefix = 'wp_';
        public array $rows = [];
        public array $sql = [];
        public bool $fail = false;
        public bool $race = false;
        public bool $schema = false;
        public function prepare(string $sql, mixed ...$args): string { return json_encode([$sql, $args]); }
        public function query(string $prepared): int|false
        {
            [$sql, $args] = json_decode($prepared, true);
            $this->sql[] = $sql;
            if ($this->fail) { return false; }
            if (str_starts_with($sql, 'INSERT IGNORE')) {
                [$key, $provider, $type, $hash, $owner, $created, $updated] = $args;
                if (isset($this->rows[$key])) { return 0; }
                $this->rows[$key] = ['provider' => $provider, 'event_type' => $type, 'payload_hash' => $hash, 'owner_id' => $owner, 'state' => 'processing', 'attempts' => 1];
                return 1;
            }
            if (str_contains($sql, 'attempts = attempts + 1')) {
                [$owner, $time, $key, $hash] = $args;
                if ($this->race) { $this->rows[$key]['state'] = 'processing'; $this->race = false; }
                if ($this->rows[$key]['state'] !== 'retryable' || $this->rows[$key]['payload_hash'] !== $hash) { return 0; }
                $this->rows[$key]['state'] = 'processing';
                $this->rows[$key]['owner_id'] = $owner;
                ++$this->rows[$key]['attempts'];
                return 1;
            }
            [$state, $time, $key, $owner] = $args;
            if (($this->rows[$key]['state'] ?? '') !== 'processing' || $this->rows[$key]['owner_id'] !== $owner) { return 0; }
            $this->rows[$key]['state'] = $state;
            return 1;
        }
        public function get_row(string $prepared, mixed $format): ?array
        {
            [$sql, $args] = json_decode($prepared, true);
            return $this->rows[$args[0]] ?? null;
        }
        public function get_charset_collate(): string { return 'DEFAULT CHARACTER SET utf8mb4'; }
        public function esc_like(string $value): string { return $value; }
        public function get_var(string $sql): ?string { return $this->schema ? 'wp_jp_webhook_events' : null; }
    }

    function dbDelta(string $sql): void
    {
        $GLOBALS['jp_webhook_schema_sql'] = $sql;
        $GLOBALS['wpdb']->schema = ! ($GLOBALS['jp_webhook_schema_fail'] ?? false);
    }

    class WP_REST_Request
    {
        public function __construct(private string $body, private array $headers = []) {}
        public function get_body(): string { return $this->body; }
        public function get_headers(): array { return $this->headers; }
    }
    class WP_REST_Response
    {
        public function __construct(public array $data, public int $status) {}
    }
}

namespace JouvencePara\Core\Webhooks {
    function apply_filters(string $hook, mixed $value): mixed { return $GLOBALS['jp_webhook_adapters'] ?? $value; }
    function register_rest_route(string $namespace, string $route, array $args): void
    {
        $GLOBALS['jp_webhook_routes'][$namespace . $route] = $args;
    }
}
