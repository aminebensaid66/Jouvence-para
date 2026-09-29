# Webhook framework — JP-WEBHOOK-001

Implements REQ-WEBHOOK-001/002/003. No live payment/carrier provider is selected or
enabled by this ticket. A version-3 repeatable migration creates
`{$wpdb->prefix}jp_webhook_events`; records survive plugin deactivation/rollback.

## Adapter contract

Core integration code registers reviewed `WebhookAdapter` instances through the
`jp_webhook_adapters` filter, keyed by a stable lowercase provider slug (maximum
40 ASCII characters, letters/digits/underscore/hyphen). An empty registry exposes
no webhook routes. Each configured adapter receives POST requests at
`/wp-json/jouvence-para/v1/webhooks/<provider>`.

`verify(rawBody, headers)` must verify the provider's documented signature over
the exact raw body, validate timestamps/replay windows where provided, and only
then return a `VerifiedEvent` with the authentic stable provider event ID and
controlled event type. Header values are arrays; native WordPress names are
lowercase with underscores (`X-Signature` becomes `x_signature`). Invalid
verification returns null. Credentials stay in
approved secret configuration, not the event table or payload. Raw request bodies
larger than 64 KiB are rejected before invoking the adapter. Routes use provider
signature authentication; WordPress session cookies/nonces are not a substitute.

`process(event, key)` receives a stable SHA-256 idempotency key scoped to provider
and event ID. Integrations must apply domain effects idempotently with this key,
including remote calls, jobs and multi-step effects. Use WooCommerce services/APIs
for commerce effects. Return one fixed outcome:

| Outcome | Meaning | HTTP | Automatic redelivery |
|---|---|---|---|
| `succeeded` | Effects confirmed complete | 200 | Acknowledge without processing again |
| `retryable` | Known failure, safe to replay with the same key | 503 | May acquire a new attempt |
| `permanent` | Invalid/unsupported domain event; no retry can fix it | 422 | Return terminal failure without processing again |
| `uncertain` | Exception or invalid result; effects may have happened | 503 | Quarantined; reconcile before recovery |

The framework does not invent order/payment/shipment transitions or provider
retry schedules. Provider-specific adapters must document their event mapping,
signature algorithm, timestamp tolerance, retry policy, timeout, receipt/side-effect
idempotency, observability and reconciliation procedures before enabling routes.
The HMAC test adapter is a fixture, not a production signature implementation.

## Durable claims and failures

- SHA-256 of provider plus event ID is the table primary key. Only one atomic
  insert claims a new event; simultaneous requests cannot both own it.
- Store a SHA-256 raw-body fingerprint. Reuse of an event ID with a different
  verified body returns 409, requiring provider-specific investigation.
- Only `retryable` can be reacquired, using a conditional SQL update. Racing
  claimants receive 503. Each attempt has a random owner ID and completion checks
  that owner, so a delayed former attempt cannot overwrite a new attempt's state.
  No read-then-write unchecked lock acquisition.
- Persist only provider slug, controlled event type, fingerprints, state, attempt
  count, attempt owner ID and UTC creation/update times. No raw event IDs/bodies/headers, customer
  data, signature values, credentials, payment data or exception messages.
- Database claim failure returns 503 without effects. Completion persistence
  failure returns 503 and leaves the existing claim locked. A crash leaves
  `processing`; it is never automatically expired and replayed.
- Unknown processing and `uncertain` records require reconciliation of the domain
  receipt/effects before any operator-approved retry. Do not delete records, rotate
  keys or clear claims merely because a request timed out. Recovery tooling and
  receipts belong to the adapter that can determine whether effects happened.
- Delivery retries reuse the stable key. This claim table cannot alone guarantee
  exactly-once remote effects across a crash; adapter/service receipts are required.

Operations can inspect state/attempt/timestamp records with restricted WP-CLI/DB
access. There is no public list/recovery endpoint or raw-payload logging. Retention
must preserve duplicate protection for each provider's delivery horizon; the final
retention policy is JP-PRIV-001. Do not prune active/uncertain records.

## Verification and rollback

Automated tests cover rejected signatures and stale/tampered signed bodies through
the REST callback; terminal duplicates; different-payload conflicts; safe retries;
unknown handler failures; database claim/completion failures; migration replay and
failure; and conditional claim races through explicit database fixtures.

Required focused checks on staging with the actual MySQL/WooCommerce installation:

1. Upgrade from schema version 2. Inspect the unique primary key and recovery index;
   repeat activation/upgrade and confirm no lost rows or repeated schema advance.
2. With no adapter, confirm there is no `/webhooks/<provider>` route.
3. With a reviewed sandbox adapter, exercise valid, invalid, tampered and expired
   signatures and confirm only verified requests create an event record.
4. Deliver the same signed event concurrently from separate HTTP/DB connections;
   confirm a single effect, stable key, terminal duplicate acknowledgement and
   serialized safe retries. Fixtures are not a live database concurrency test.
5. Interrupt processing after an effect, or fail completion persistence. Confirm
   subsequent deliveries do not repeat effects; reconcile the adapter receipt.
6. Inspect the event table and request/error logging for payloads/secrets/PII.
   Check the provider's treatment of 422/503 before enabling production.

These live checks are documented, not claimed as executed. Rollback by reverting
this commit removes routes/framework wiring; retain migration table/data for
reconciliation. No commerce data/status is changed by framework rollback.
