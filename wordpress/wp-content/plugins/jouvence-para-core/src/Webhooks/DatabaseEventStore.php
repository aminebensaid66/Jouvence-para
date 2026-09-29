<?php

declare(strict_types=1);

namespace JouvencePara\Core\Webhooks;

final class DatabaseEventStore implements EventStore
{
    public function claim(string $key, string $provider, string $type, string $payloadHash, string $owner): string
    {
        global $wpdb;
        if (! isset($wpdb) || ! method_exists($wpdb, 'query') || ! method_exists($wpdb, 'get_row')) {
            return 'unavailable';
        }
        $table = $wpdb->prefix . 'jp_webhook_events';
        $now = gmdate('Y-m-d H:i:s');
        $inserted = $wpdb->query($wpdb->prepare(
            "INSERT IGNORE INTO {$table} (event_key, provider, event_type, payload_hash, owner_id, state, attempts, created_at, updated_at) VALUES (%s, %s, %s, %s, %s, 'processing', 1, %s, %s)",
            $key, $provider, $type, $payloadHash, $owner, $now, $now
        ));
        if ($inserted === 1) {
            return 'claimed';
        }
        if ($inserted === false) {
            return 'unavailable';
        }
        $row = $wpdb->get_row($wpdb->prepare("SELECT payload_hash, state FROM {$table} WHERE event_key = %s", $key), ARRAY_A);
        if (! is_array($row)) {
            return 'unavailable';
        }
        if (! hash_equals($row['payload_hash'], $payloadHash)) {
            return 'conflict';
        }
        if ($row['state'] === 'retryable') {
            $claimed = $wpdb->query($wpdb->prepare(
                "UPDATE {$table} SET state = 'processing', owner_id = %s, attempts = attempts + 1, updated_at = %s WHERE event_key = %s AND state = 'retryable' AND payload_hash = %s",
                $owner, $now, $key, $payloadHash
            ));
            return $claimed === 1 ? 'claimed' : ($claimed === false ? 'unavailable' : 'busy');
        }
        return in_array($row['state'], ['succeeded', 'permanent'], true) ? $row['state'] : 'busy';
    }

    public function finish(string $key, string $state, string $owner): bool
    {
        global $wpdb;
        if (! in_array($state, ['succeeded', 'retryable', 'permanent', 'uncertain'], true)
            || ! isset($wpdb) || ! method_exists($wpdb, 'query')) {
            return false;
        }
        $table = $wpdb->prefix . 'jp_webhook_events';
        return $wpdb->query($wpdb->prepare(
            "UPDATE {$table} SET state = %s, updated_at = %s WHERE event_key = %s AND state = 'processing' AND owner_id = %s",
            $state, gmdate('Y-m-d H:i:s'), $key, $owner
        )) === 1;
    }
}
