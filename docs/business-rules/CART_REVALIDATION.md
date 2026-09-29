# Cart revalidation — JP-CART-002

Implements REQ-CART-003 in the core plugin. WooCommerce owns session mutation,
prices, coupon allocation, taxes, shipping, native totals and order persistence.
The business owner approved native stock timing on 2026-09-29 (ADR-0005); this
ticket checks availability and does not reserve/reduce/restore stock.

## Price checks

- Capture the current catalog price in private native cart-session line data after
  WooCommerce generates the cart key. A price change cannot create separate keys
  for otherwise identical products.
- On session load and before totals calculation, read fresh WooCommerce products,
  refresh their cart data and update the catalog baseline. Preserve quantity,
  options and coupon information; WooCommerce calculates all financial totals.
  Cart-load price/baseline changes explicitly trigger native totals/session updates,
  since WooCommerce may otherwise retain previously saved totals.
- Invalidate relevant product/parent post and metadata caches before constructing
  products at each critical check. A new object alone can read stale request caches.
  Invalidate native product type groups and, when enabled/available, WooCommerce
  10.5+ product instance caching through its guarded cache service.
- Compare prices using native currency precision. Existing carts without a baseline
  establish one without inventing an unknown previous price.
- Display one native notice when a price changes. A checkout request that observes
  a changed price is rejected so the customer reviews the updated cart and submits
  again; final order creation checks again after totals were calculated.
  A late change recalculates/persists the native cart total immediately; the already
  built order is still rejected because its lines may contain the previous price.
- The baseline describes catalog price, not a tax/coupon-adjusted line total. Future
  cart-specific promotion/pack services must apply after the refresh priority 5 and
  supply explicit contracts instead of persisting stale product object prices.

## Stock checks

- Load fresh product/variation data; require purchase eligibility, in-stock status,
  managed inventory and positive whole-unit quantities.
- Aggregate all cart lines by the actual stock owner, including variations sharing
  a parent and repeated lines with different options. Compare combined demand
  against physical stock minus the approved safety buffer and native held stock.
- Checkout excludes only the current native session order/draft; final checks use
  the actual order ID. Other live reservations continue to count.
- Retain quantities/options on the remaining native cart lines and provide a generic native error: reduce the
  quantity or remove the unavailable item. Never reveal exact stock or silently
  change the customer's quantities.
  WooCommerce's existing session loader may remove deleted/unpublished/unpurchasable
  or changed-hash lines before this module runs; its native behavior is retained.
- Final hooks also check actual WooCommerce order-line quantities; an invalid cart
  or stale final order throws before payment proceeds. Store API uses HTTP 409.
- Classic cart/checkout and Cart/Checkout blocks have dedicated validation hooks.
  WooCommerce remains responsible for reservation locking. Concurrent last-unit
  buffer enforcement/reservation lifecycle tests are JP-STOCK-002; a PHP cart
  availability check alone cannot serialize concurrent checkout requests.

## Focused staging verification

1. Add a product, change its catalog price from another session, then reload the
   classic cart and Cart block. Confirm the new native total and a visible notice;
   repeated refreshes must not accumulate notices or duplicate cart lines.
2. Re-add the identical product after a price change; verify native cart-key merge
   and the combined stock limit. Repeat with distinct options and parent-managed
   variations. Check coupon/tax/shipping totals remain native and correct.
3. Make a product unavailable or reduce physical/held stock after loading checkout.
   Both checkout implementations must show a resolution and avoid payment.
4. Change price/stock between totals and final order creation. Classic checkout must
   reject the request; Store API must return a safe conflict and no payment effect.
   Review current totals and retry with sufficient stock: ordering succeeds.
5. Retry an existing checkout/draft with its own reservation. Confirm it does not
   count its own hold twice but still respects other orders and the safety buffer.
6. Confirm errors are keyboard/screen-reader reachable through native WooCommerce
   notices/blocks, with no exact quantity disclosed. Exercise the supported live
   WooCommerce versions, HPOS and classic/block routes.

The automated suite uses explicit WordPress/WooCommerce fixtures. These runtime
steps are documented, not claimed as executed on production. Rollback: revert this
ticket; its private session baseline is inert and can expire natively. No custom
database table, migration, stock write or order-status transition is introduced.
