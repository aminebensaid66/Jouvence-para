<?php

declare(strict_types=1);

namespace JouvencePara\Core\Infrastructure\Migrations;

use RuntimeException;

final class AuditLogMigration implements Migration
{
    public function version(): int
    {
        return 2;
    }

    public function up(): void
    {
        global $wpdb;

        if (! isset($wpdb) || ! property_exists($wpdb, 'prefix')) {
            throw new RuntimeException('WordPress database access is unavailable for the audit-log migration.');
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $table = $wpdb->prefix . 'jp_audit_log';
        $charset = method_exists($wpdb, 'get_charset_collate') ? $wpdb->get_charset_collate() : '';

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            occurred_at datetime NOT NULL,
            actor_id bigint(20) unsigned NOT NULL DEFAULT 0,
            action varchar(100) NOT NULL,
            object_type varchar(80) NOT NULL,
            object_id varchar(191) NOT NULL DEFAULT '',
            before_json longtext NULL,
            after_json longtext NULL,
            environment varchar(32) NOT NULL,
            PRIMARY KEY  (id),
            KEY occurred_at (occurred_at),
            KEY object_lookup (object_type, object_id),
            KEY action (action)
        ) {$charset};";

        dbDelta($sql);

        if (! method_exists($wpdb, 'get_var') || ! method_exists($wpdb, 'prepare') || ! method_exists($wpdb, 'esc_like')) {
            throw new RuntimeException('Unable to verify the audit-log database migration.');
        }

        $found = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));
        if ($found !== $table) {
            throw new RuntimeException('The audit-log table was not created successfully.');
        }
    }
}
