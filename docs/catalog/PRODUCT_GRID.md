# Responsive product grid — JP-DISC-001

The theme owns archive/card markup. WooCommerce remains the source for price, sale display, visibility and purchasability. Cards use `woocommerce_thumbnail`, lazy loading and responsive `sizes`; exact stock quantities are never read or rendered.

Quick add is limited to simple, purchasable, priced and in-stock products. Variable/non-purchasable/out-of-stock products link to their detail page instead. Filtering and sorting are separate issues and are not implemented by this patch.
