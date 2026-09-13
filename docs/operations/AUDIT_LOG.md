# Audit Log — JP-AUDIT-001

Jouvence Para stores append-oriented audit events in the `jp_audit_log` table created by schema migration version 2.

Initial event coverage includes:

- product regular/sale price and sale-window changes;
- product stock quantity/status changes;
- coupon create/update events;
- order status transitions;
- refund creation;
- customer/user record administration and role changes;
- shipping/payment WooCommerce configuration changes.

Every row records UTC time, actor user ID, action, object type/ID, before/after snapshots where safely available, and WordPress environment type. Product metadata is captured before WordPress mutates it so price and stock entries contain genuine previous and replacement values.

The schema migration verifies that WordPress created the audit table before advancing the stored schema version. A failed table creation therefore remains visible and retryable instead of silently marking the migration complete.

Snapshots pass through the shared observability redactor. Callers must still avoid supplying passwords, cookies, raw card details, tokens or unnecessary customer data. Payment/shipping option changes deliberately record that a configuration changed rather than persisting potentially secret option values.

Authorized WooCommerce managers can review the latest audit events under WooCommerce → Audit log. There is intentionally no edit/delete UI for individual audit rows.
