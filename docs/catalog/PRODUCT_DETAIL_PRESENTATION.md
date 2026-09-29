# Product detail presentation — JP-PDP-001

The theme owns the page shell while WooCommerce public hooks own gallery, title, prices, excerpt, stock-aware cart controls, native variation form, meta, tabs and related products. This preserves WooCommerce variation price/purchasability behavior and prevents a second commerce implementation.

The controlled brand is rendered before the title. The existing media pipeline and WooCommerce gallery sizes handle optimized responsive images. Optional WooCommerce sections are emitted only when their hooks have content. Exact stock quantities remain suppressed by JP-STOCK-001.

## Related and complementary products — JP-PDP-003

- WooCommerce's native **Up-sells** field is the manually curated complementary-products list. It renders under the “Compléments de la routine” heading and uses product menu order instead of random order.
- Related products remain a separate WooCommerce category/tag-based list under “Produits similaires”. WooCommerce excludes the product's up-sell IDs from that list; the theme disables random shuffling and uses ascending product menu order.
- WooCommerce core remains the only Product/Offer/AggregateRating JSON-LD generator. Its product and offer values come from the same `WC_Product` used by the visible title, price and stock controls. AggregateRating remains conditional on enabled native ratings and a non-zero rating count. Do not add a second product-schema renderer to the theme.
