# Checkout cost, terms and delivery summary — JP-CHECKOUT-002

WooCommerce remains the source of truth for the checkout review table, selected
payment method, discounts, taxes, shipping totals and final order total. The
classic checkout and Checkout Block recalculate through WooCommerce when the
customer changes an address or delivery method. Do not duplicate these totals in
the theme.

First Delivery uses the approved nationwide matrix and rate. Its checkout label
includes the approved estimate of two working days. The configured shipping
rate and resulting total are shown in WooCommerce's native order review.

## Required store configuration

Before accepting real orders, an authorized store operator must:

1. Publish the approved Terms and Conditions page and select it in
   **WooCommerce → Settings → Advanced → Page setup → Terms and conditions**.
2. For the Checkout Block, keep its Terms and Conditions block present and its
   **Require Checkbox** option enabled. The classic checkout uses WooCommerce's
   required checkbox when a Terms and Conditions page is selected.
3. Confirm the checkbox starts unchecked, links to the published page, and
   prevents submission until checked. Do not insert legal policy text in code.
4. Test with the approved geography/rate: change the governorate and shipping
   method, wait for WooCommerce to recalculate, and compare displayed shipping
   and total against the selected rate. Confirm the order summary includes
   products, quantities, discounts, shipping, total, payment method and delivery
   estimate before submission.

The delivery window is an estimate, not a guaranteed date. Tax treatment of the
shipping charge remains controlled by WooCommerce tax settings as recorded in
ADR-0006.

## Verification

The plugin integration test checks that First Delivery publishes the approved
estimate, charges 7.000 TND below the 200.000 TND post-discount threshold, is
free at the threshold, and does not offer a rate outside Tunisia. A focused
isolated WordPress 7.1 / WooCommerce 11.1.2 runtime check used a synthetic
10.000 TND product and a temporary nonlegal terms page. Changing the Store API
customer address from Tunis to Sfax recalculated the same approved national
rate: 7.000 TND shipping and 17.000 TND total. The classic checkout review
rendered the product, quantity, shipping estimate and amount, total, enabled
cash-on-delivery method and terms link. The checkbox rendered unchecked, and a
checkout POST without acceptance returned WooCommerce's required-terms error
without creating an order. No real order or email was sent.

The runtime fixture did not verify the Checkout Block UI or use owner-approved
legal terms. Before real orders, the owner must publish/select the applicable
terms page and verify the active checkout page, including the Checkout Block's
required-checkbox setting when that block is used.
