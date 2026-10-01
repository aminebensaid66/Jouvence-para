# Customer wishlist — JP-WISH-001

The wishlist is available to authenticated accounts only. Guests do not receive
an anonymous list, so no guest-to-account merge policy is needed.

WooCommerce customer metadata stores the owner's product ID list, with a
maximum of 100 saved products. The list does not copy product names, prices,
stock or customer contact details. Product
visibility and current purchase availability are read from WooCommerce whenever
the list is displayed. Items that are no longer public are shown as unavailable
without exposing their former product details and can still be removed.

Adding an existing product and removing a missing product are safe retries.
Products are added to the cart through WooCommerce only when they are public,
purchasable, in stock and simple.
Variable and grouped products link to their product page for option selection;
other purchasable product types also link to their product page. A cart action
uses a bounded, session-scoped replay ledger containing the most recent 32
submissions. Replaying the same form while the product remains in the cart does
not add it twice; replay after removal adds it again. This guard is best-effort
across concurrent requests and session storage failures, not a transactional
exactly-once guarantee.

Deleting a customer account relies on WordPress/WooCommerce customer-data cleanup.
The approved privacy retention/export/erasure policy remains owned by JP-PRIV-001.
