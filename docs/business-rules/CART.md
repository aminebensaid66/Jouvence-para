# Cart behavior

## Source of truth

WooCommerce owns cart contents, customer sessions, saved customer carts, product prices, coupons, and cart totals. The theme renders WooCommerce cart data and controls. Jouvence Para does not copy cart contents or totals into a custom table, cookie, or user meta field.

Price and stock revalidation at cart load, checkout, and order creation belong to JP-CART-002. Coupon and shipping rules remain controlled by their respective promotion and shipping configuration; the cart displays those values when WooCommerce can calculate them.

## Guest and authenticated carts

- Anonymous-cart persistence follows WooCommerce's native session behavior and configured session-expiration filters. Jouvence Para does not set a separate retention period.
- Authenticated cart persistence and guest-to-customer cart merging use WooCommerce's native persistent-cart behavior.
- Logout behavior remains native WooCommerce behavior. No custom cart copy or deletion runs on login or logout.

## Presentation

The cart page uses WooCommerce's native cart page, template, or block. The theme provides a header cart link with a current item count. WooCommerce cart fragments refresh it after AJAX add-to-cart actions, and the native Cart block data store refreshes it after block quantity changes and removals. Standard WooCommerce controls continue to handle cart mutations.

## Focused verification

For each supported WooCommerce release, verify a simple in-stock product can be added, remains in the guest cart after navigation and refresh, has its quantity updated, and can be removed. Verify that a guest cart is merged with an existing saved customer cart on login, that the authenticated cart is restored in a later session, and that logout leaves the native guest-cart behavior intact. Verify totals from WooCommerce and confirm a shipping estimate appears only when an applicable rate is configured.
