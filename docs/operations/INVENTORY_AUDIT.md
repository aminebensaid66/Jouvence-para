# Inventory audit — JP-STOCK-003

Implements REQ-STOCK-006 using the existing append-oriented `jp_audit_log`
repository. No migration, stock writes, order transitions or reservation changes.

## Coverage and ownership

- Native WooCommerce CRUD/admin/quick edit/bulk edit/REST/import stock saves use
  `woocommerce_{product,variation}_before_set_stock` and `..._set_stock`.
- `wc_update_product_stock()` uses the same hooks despite updating stock through
  the WooCommerce data store rather than WordPress metadata hooks. The actual
  stock-owning product or variation supplied by WooCommerce is the audit object.
- Read persisted `_stock` before mutation: a CRUD object may already contain the
  new proposed quantity. Normalize numeric storage values; empty quantity is null.
- Keep metadata fallback coverage for product/variation quantity and stock status,
  and existing price/promotion audit behavior. Suppress quantity metadata events
  during a native stock update so a successful change produces one quantity row.
- Equal quantities, repeated completion notifications and repeated saves at the
  same quantity produce no quantity row. A later real change produces a new row.
  Audit logging does not deduplicate or authorize the stock mutation itself.
- Automated changes are also recorded. Actor is the current WordPress user ID;
  zero means no authenticated user. Do not infer a human from an automated job.

Each quantity row records `product_stock_changed`, product/variation ID and type,
actor, UTC timestamp, environment and before/after `_stock` values. Existing status
events remain separate. Unknown prior metadata values are null, not invented zero.

## Optional reason

The classic simple-product inventory panel and each variation inventory panel
provide a labeled optional reason. It applies only to that product's quantity
change in the current save, with its own product-bound nonce and `edit_post` plus
`edit_products` authorization. It is blank on reload and never saved as product
metadata. Saves without a valid reason still audit the actual stock change.

Reasons are sanitized, passed through the existing redactor and limited to 500
Unicode characters and the shared 1000-byte audit-text budget, preserving complete
Unicode characters. Staff must not enter customer details, passwords, tokens or
other secrets. Quick/bulk edit, REST, imports and external integrations have no
reason-entry interface in this ticket; their changes are still audited.

Only users with `manage_woocommerce` can open **WooCommerce → Audit log**. The
handler checks authorization before querying rows and escapes all displayed
values. There is no public endpoint or row edit/delete interface.

## Focused runtime verification (required on staging before release)

1. As an authorized editor, change a simple product from 12 to 8 with a reason.
   Check one quantity row: your user ID, correct product, 12 → 8, reason and UTC
   time. Repeat the save without changing quantity: no additional quantity row.
2. Edit a variation's own stock with a reason. Check its variation ID and values.
   Save a second variation with no reason: the first reason must not leak.
3. Exercise quick edit, bulk edit, authenticated REST/import and
   `wc_update_product_stock($product, 1, 'decrease')`. Check before/after values,
   one quantity row per actual change and the actual stock owner for shared stock.
4. Submit a malformed or invalid reason nonce. Stock still follows native
   WooCommerce authorization; no forged reason appears in its audit event.
5. Try the audit URL as anonymous/customer/product editor lacking
   `manage_woocommerce`: access denied. Check escaped reason text as a manager.
6. Check unchanged price/stock/order behavior and that the audit table exists
   (schema migration version 2). Investigate database failures through operations;
   this logger does not block native saves or guarantee delivery during DB failure.

The dependency-free integration suite exercises hook order with explicit fixtures;
it does not execute a live WooCommerce REST/admin/import request. Runtime steps
above are documented, not claimed as executed in production.

Rollback: revert this ticket's commit. Existing audit rows remain intact and the
previous logger resumes. Audit retention remains governed by JP-PRIV-001.
