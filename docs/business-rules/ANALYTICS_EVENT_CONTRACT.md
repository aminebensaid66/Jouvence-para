# Analytics event contract — JP-AN-001

Analytics is an observation layer. WooCommerce remains the source of truth for
products, carts and orders. The browser emits the following stable event names:

`search`, `search_no_results`, `search_result_click`, `category_view`, `filter_use`, `product_view`,
`whatsapp_click`, `add_to_cart`, `remove_from_cart`, `cart_view`,
`checkout_start`, `checkout_error`, `shipping_selected`, `payment_selected`,
`purchase`, `coupon_applied`, `coupon_rejected`, `newsletter_signup`,
`back_in_stock_request`, `account_registration`, and `reorder`.

Every event carries `schema_version: 1` and an `event` name. Product and purchase
payloads use stable product ID, SKU, quantity, price and currency fields. Search
payloads contain only query length and result count. Search phrases are freeform
and can contain health or contact details, so they are never transmitted. A
`search_result_click` contains only the selected product ID. Purchase payloads contain
an opaque salted purchase ID, order value, currency, coupon count and line items;
they do not contain names, addresses, email addresses, phone numbers, account IDs,
payment data or order keys. Invalid names, control characters, oversized strings,
objects and direct personal-identifier fields are dropped by the PHP contract.

The existing consent module is the gate. No analytics event is dispatched before
the visitor allows the analytics category. Consent changes are handled by the
existing `jouvencepara:consentchange` flow; deferred scripts remain inactive until
consent is granted. Marketing consent is separate.

Classic WooCommerce add/remove and registration events are queued in the WC
session only after analytics consent; AJAX and Store API cart changes are observed
in the browser. Checkout remains native; the browser observes UI state without
reimplementing order creation. Purchase details are prepared only for processing,
on-hold or completed orders and only when the logged-in owner or a visitor with
the matching order key can view the order. The opaque purchase ID is persisted
before dispatch to prevent duplicate counts on refresh. Purchase is suppressed if
browser storage is unavailable, since refresh deduplication cannot be guaranteed.

## Verification

Unit and runtime tests cover the event allow-list, nested product items, bounded
values, control characters, direct PII keys, consent denial, cart-state events,
purchase access checks and refresh deduplication. External analytics providers
are intentionally not selected here; a future provider adapter must consume this
contract only after consent. Newsletter, back-in-stock, reorder and coupon
rejection features can call the public `JouvenceParaAnalytics.emit` contract when
their source interactions are implemented; no personal form fields are included.

Rollback is a code revert. Existing consent cookies remain valid and no analytics
database or customer metadata is introduced.
