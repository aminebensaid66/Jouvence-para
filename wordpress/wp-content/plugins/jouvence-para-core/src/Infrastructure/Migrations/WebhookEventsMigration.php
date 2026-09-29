<?php

declare(strict_types=1);

namespace JouvencePara\Core\Infrastructure\Migrations;

use RuntimeException;

final class WebhookEventsMigration implements Migration
{
    public function version(): int { return 3; }

    public function up(): void
    {
        global $wpdb;
        if (! isset($wpdb) || ! property_exists($wpdb, 'prefix')) {
            throw new RuntimeException('WordPress database access is unavailable for webhook migration.');
        }
        if (! function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }
        $table = $wpdb->prefix . 'jp_webhook_events';
        $charset = $wpdb->get_charset_collate();
        dbDelta("CREATE TABLE {$table} (
            event_key char(64) NOT NULL,
            provider varchar(40) NOT NULL,
            event_type varchar(80) NOT NULL,
            payload_hash char(64) NOT NULL,
            owner_id char(32) NOT NULL,
            state varchar(16) NOT NULL,
            attempts bigint(20) unsigned NOT NULL DEFAULT 1,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (event_key),
            KEY recovery (state, updated_at)
        ) {$charset};");
        $found = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));
        if ($found !== $table) {
            throw new RuntimeException('The webhook-events table was not created successfully.');
        }
    }
}
