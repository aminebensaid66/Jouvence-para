<?php

declare(strict_types=1);

namespace JouvencePara\Core\Audit;

final class AuditRepository
{
    /** @param array<string, mixed> $before @param array<string, mixed> $after */
    public function record(
        string $action,
        string $objectType,
        string|int $objectId,
        array $before = [],
        array $after = []
    ): void {
        global $wpdb;
        if (! isset($wpdb) || ! method_exists($wpdb, 'insert')) {
            return;
        }

        $table = $wpdb->prefix . 'jp_audit_log';
        $beforeJson = wp_json_encode(AuditEvent::snapshot($before), JSON_UNESCAPED_SLASHES);
        $afterJson = wp_json_encode(AuditEvent::snapshot($after), JSON_UNESCAPED_SLASHES);

        $wpdb->insert(
            $table,
            [
                'occurred_at' => gmdate('Y-m-d H:i:s'),
                'actor_id' => get_current_user_id(),
                'action' => AuditEvent::action($action),
                'object_type' => AuditEvent::objectType($objectType),
                'object_id' => AuditEvent::objectId($objectId),
                'before_json' => is_string($beforeJson) ? $beforeJson : '{}',
                'after_json' => is_string($afterJson) ? $afterJson : '{}',
                'environment' => wp_get_environment_type(),
            ],
            ['%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s']
        );
    }

    /** @return list<array<string, mixed>> */
    public function latest(int $limit = 100): array
    {
        global $wpdb;
        if (! isset($wpdb) || ! method_exists($wpdb, 'get_results')) {
            return [];
        }

        $limit = max(1, min(200, $limit));
        $table = $wpdb->prefix . 'jp_audit_log';
        $sql = $wpdb->prepare("SELECT * FROM {$table} ORDER BY id DESC LIMIT %d", $limit);
        $rows = $wpdb->get_results($sql, ARRAY_A);
        return is_array($rows) ? $rows : [];
    }
}
