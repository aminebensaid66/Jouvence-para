<?php

declare(strict_types=1);

namespace {
    final class JP_Test_Audit_Database
    {
        public string $prefix = 'wp_';
        public array $rows = [];
        public int $reads = 0;

        public function insert(string $table, array $row, array $formats): int
        {
            $this->rows[] = $row;
            return 1;
        }
        public function prepare(string $sql, int $limit): string { return str_replace('%d', (string) $limit, $sql); }
        public function get_results(string $sql, mixed $format): array { ++$this->reads; return array_reverse($this->rows); }
    }

    function jp_test_audit_reset(): JP_Test_Audit_Database
    {
        $GLOBALS['wpdb'] = new JP_Test_Audit_Database();
        $GLOBALS['jp_audit_meta'] = [];
        $GLOBALS['jp_audit_types'] = [41 => 'product', 42 => 'product_variation'];
        $GLOBALS['jp_test_capability'] = true;
        $GLOBALS['jp_audit_actor'] = 7;
        $GLOBALS['jp_audit_nonce_valid'] = true;
        $GLOBALS['jp_audit_fields'] = [];
        $_POST = [];
        return $GLOBALS['wpdb'];
    }
}

namespace JouvencePara\Core\Audit {
    function get_post_meta(int $id, string $key, bool $single): mixed { return $GLOBALS['jp_audit_meta'][$id][$key] ?? ''; }
    function get_post_type(int $id): string { return $GLOBALS['jp_audit_types'][$id] ?? 'post'; }
    function get_current_user_id(): int { return $GLOBALS['jp_audit_actor']; }
    function wp_get_environment_type(): string { return 'testing'; }
    function wp_verify_nonce(string $nonce, string $action): bool
    {
        return $GLOBALS['jp_audit_nonce_valid'] && $nonce === 'valid' && preg_match('/^jp_stock_reason_[0-9]+$/', $action) === 1;
    }
    function wp_die(string $message): never { throw new \RuntimeException($message); }
    function wp_nonce_field(string $action, string $name): void { $GLOBALS['jp_audit_fields'][] = [$action, $name]; }
    function woocommerce_wp_text_input(array $field): void { $GLOBALS['jp_audit_fields'][] = $field; }
    function esc_html_e(string $text, string $domain): void { echo \esc_html($text); }
    function esc_html__(string $text, string $domain): string { return \esc_html($text); }
}
