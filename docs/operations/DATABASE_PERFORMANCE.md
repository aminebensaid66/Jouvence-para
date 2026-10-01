# Database performance diagnostics — JP-DB-002

This repository uses MySQL 8.4 in its reference Compose environment. The
database service explicitly enables MySQL Performance Schema. Production MySQL
must also have Performance Schema enabled and statement digest instrumentation
available; confirm this with the database operator during deployment. If the
managed service does not expose `performance_schema`, use its normalized query
insights facility with equivalent access and data-redaction controls.

## Query and index review

The application has two custom tables. Their current query paths and schema
indexes are:

| Table | Current query pattern | Supporting index |
| --- | --- | --- |
| `{$wpdb->prefix}jp_webhook_events` | Claim, read and conditionally update one event by `event_key` | Primary key on `event_key` |
| `{$wpdb->prefix}jp_audit_log` | Show newest audit rows with `ORDER BY id DESC LIMIT n` | Primary key on `id` |

The webhook migration also defines `(state, updated_at)` for recovery-state
inspection. The current application does not scan that pair; recovery tooling
must confirm its query plan and operational use before relying on it. The audit
migration defines secondary indexes for `occurred_at`, `(object_type,
object_id)`, and `action`; current application code does not filter on those
columns. These are existing schema choices, not a reason to add further
indexes. Review their production usage before changing a released migration.

Product, search, cart, checkout and order data remain in WooCommerce's native
tables and query APIs. Custom code does not add product/order SQL or indexes to
those tables. No new index is justified by the current custom query inventory.

For any proposed index, record the observed query digest, production-like row
counts, `EXPLAIN` plan, staging before/after latency and rows examined, and the
write/storage cost. Use a versioned migration for an approved custom-table
index. Never add an index from a guessed query pattern or edit a released
migration.

## Production-safe slow-query investigation

Use MySQL Performance Schema statement digests to inspect aggregate counts,
latency and rows examined. Digest text normalizes data values, so this workflow
does not require storing query bind values in WordPress logs, the MySQL general
log, or an application monitoring system.

First confirm the feature is available:

```sql
SHOW GLOBAL VARIABLES LIKE 'performance_schema';
SELECT COUNT(*) AS digest_rows
FROM performance_schema.events_statements_summary_by_digest;
```

Run the following with a restricted database operator account that can read
the relevant Performance Schema summary. It reports normalized query shapes,
not raw parameter values:

```sql
SELECT
  SCHEMA_NAME,
  DIGEST_TEXT,
  COUNT_STAR AS executions,
  ROUND(SUM_TIMER_WAIT / 1000000000000, 2) AS total_seconds,
  ROUND(AVG_TIMER_WAIT / 1000000000, 2) AS average_ms,
  ROUND(MAX_TIMER_WAIT / 1000000000, 2) AS maximum_ms,
  SUM_ROWS_EXAMINED AS rows_examined,
  SUM_ROWS_SENT AS rows_returned
FROM performance_schema.events_statements_summary_by_digest
WHERE SCHEMA_NAME = DATABASE()
  AND DIGEST_TEXT IS NOT NULL
ORDER BY SUM_TIMER_WAIT DESC
LIMIT 20;
```

Interpret these values over a stated observation window and representative
traffic. Performance Schema summaries are in-memory and reset on server
restart; note the observation start/end and server uptime when comparing
captures. Do not persist digest output beyond the approved operational need.
Use `EXPLAIN` or `EXPLAIN ANALYZE` against representative data in staging before
changing indexes. `EXPLAIN ANALYZE` executes the statement; use it only for
read-only statements in a controlled environment.

Do not enable MySQL's general query log or WordPress `SAVEQUERIES` on production
as a substitute: raw statements and traces can expose customer or operational
values and consume disk. Do not copy SQL with literal values into tickets or
chat. If Performance Schema is disabled by a managed host, request its
normalized digest/insights view from the provider; enabling query text logging
is not the fallback.

The production host is selected and configured under JP-DEC-009. This guide
defines the required MySQL capability and investigation procedure; the
production operator must verify it on the selected host before launch.
