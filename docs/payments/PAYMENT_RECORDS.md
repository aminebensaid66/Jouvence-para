# JP-PAY-002 — Payment record contract

Implements REQ-PAY-002 and REQ-PAY-004 in the core plugin. WooCommerce owns
orders and payment methods. This foundation has no public endpoint and does
not initiate charges, refunds, COD collection, or automatic state transitions.

## Data contract

`Payments\PaymentRecord::fromArray()` accepts exactly these fields:

| Field | Contract |
| --- | --- |
| `method` | Stable WooCommerce payment-method identifier |
| `status` | Payment status code supplied by an approved domain service |
| `provider` | Provider code, or null for a payment without an external provider |
| `transaction_id` | Opaque provider reference, or null; requires a provider |
| `paid_at` | Valid UTC `YYYY-MM-DDTHH:MM:SSZ` instant, or null |
| `refund_status` | Refund status code supplied by an approved domain service |

Codes are lowercase ASCII identifiers, at most 64 characters. References are
bounded ASCII identifiers (255 characters), never card numbers or credentials.
Null means unavailable; empty values are rejected. There is no order-status
field and no inference of paid time or refund state from payment status.
Status vocabularies and transitions must be approved with the consuming
payment integration. Example test statuses are fixtures, not business policy.

The record rejects extra fields, nested provider payloads, and malformed data.
It cannot determine whether an otherwise valid opaque reference contains a
secret: adapters must extract only the provider's documented transaction ID.
Raw card data, verification codes, hosted-component payloads, provider bodies,
and credentials must never be passed to this model, storage, or logs.

## Storage and replay

`PaymentRecordStore` separates the model from storage.
`WooCommercePaymentRecordStore` uses `wc_get_order()`, `get_meta()` in edit
context, `update_meta_data()`, and `save_meta_data()`. It supports HPOS without
post IDs or direct SQL, and rejects missing orders and refund objects.

Each internal record ID (1–64 ASCII letters, digits, underscores or hyphens)
identifies one snapshot under `_jp_payment_record_<id>` order metadata. Multiple
records can coexist for payment attempts; IDs must remain stable on retries.
Writing an identical snapshot is a no-op. Writing a changed snapshot replaces
that record only. Order status, WooCommerce transaction ID, paid date and
payment method properties are not modified. No new tables or migration exist.

This is storage, not authorization or a transition engine. The consuming service
must validate the approved transition, authenticate/authorize any incoming
request, verify signatures/nonces as appropriate, serialize competing updates,
and reject stale provider events. Snapshot replay does not deduplicate charges
or refunds. No provider, webhook, checkout, or order hooks are installed by this
foundation. Existing WooCommerce COD behavior remains available.

## Logging

Use only `PaymentRecord::logContext()` for payment diagnostics. It omits the
transaction reference and paid time. Never log `toArray()`, provider bodies,
provider exceptions/messages, or credentials. The shared observability redactor
also masks structured transaction IDs, provider/payment payloads and responses,
payment records, and card verification keys recursively. Text redaction is
not a safe substitute for extracting an approved diagnostic allowlist.

## Focused manual check (not yet executed)

On a disposable WooCommerce installation, with HPOS enabled and then disabled:

1. Create a test order using WooCommerce and note its status and payment method.
2. In WP-CLI `eval-file`, construct a record containing method `cod`, status
   `pending`, null provider/reference/paid time, and refund status `none`.
3. Save through `WooCommercePaymentRecordStore` using the order ID and record
   ID `attempt_1`. Reload the order and verify `find()` returns identical data.
4. Replay the snapshot, save a second record under `attempt_2`, and verify both
   exist. Verify the order status, payment method, transaction ID and paid date
   remain unchanged.
5. Attempt a record with an extra `card_number` key (use a dummy string), an
   invalid date, and a missing order. Verify safe exceptions and no writes.
6. Inspect `logContext()` and nested `Redactor::context()` output: no transaction
   reference, provider body, verification code or credential may remain.

Automated tests cover validation, replay, multiple records, corrupt metadata,
order-state independence and redaction using a WooCommerce CRUD test double.
Real database/HPOS checks and an independent payment review are required before
connecting this foundation to a production payment integration.
