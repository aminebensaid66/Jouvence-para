# Customer authorization and data rights — JP-ACC-003

WooCommerce/WordPress own authentication, customer profile and address forms,
order endpoints, and the Tools → Export Personal Data / Erase Personal Data
request workflow. These native endpoints bind operations to the signed-in owner
or to WooCommerce's verified guest order key. Jouvence Para does not add a
customer-ID parameter or a public API for reading another customer's records.

The core plugin registers a WordPress privacy exporter and eraser for its two
private customer metadata records: the wishlist product IDs and the current
email-marketing preference/timestamp. WordPress's privacy request process
identifies the user by email and allows the operator to review the verified
request before running export or erasure. The exporter returns only those
records for the matching user. The eraser removes only those two metadata keys;
it leaves unrelated user metadata intact and reports failed deletion so it can
be retried or investigated.

Customer profile correction stays in the native WooCommerce account forms. The
WooCommerce privacy exporters/erasers remain responsible for order data. This
feature does not decide whether legally retained order or consent evidence is
deleted or anonymized; operators must follow the business-approved retention
policy before approving an erasure request. Retention periods and any legal
exceptions remain JP-PRIV-001 decisions.

## Authorization coverage

- Wishlist reads use the current customer ID; writes require that same ID, a
  logged-in customer capability and a product-specific nonce.
- Account preference writes require the current owner's ID, the native account
  nonce, a separate preference nonce and a controlled choice.
- Native order details remain behind WooCommerce ownership or its verified
  guest order-key flow.
- Analytics purchase details are emitted only to the account owner or a visitor
  presenting the matching WooCommerce order key.

Automated account/wishlist/privacy tests cover cross-user requests, owner-bound
updates, verified-email exports, normalized data, and targeted erasure. The
focused staging checklist in `CUSTOMER_ACCOUNTS.md` still applies; production
legal retention approval and the deployed WordPress/WooCommerce privacy tools
must be verified before processing live erasure requests.
