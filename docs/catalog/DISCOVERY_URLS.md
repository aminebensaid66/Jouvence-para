# Sorting, pagination and filter URLs — JP-DISC-003

Supported sort keys are `relevance`, `bestseller`, `newest`, `price`, and `price-desc`. Invalid values normalize to `relevance`. Bestseller uses WooCommerce `total_sales`; newest adds an ID tie-breaker; price directions remain WooCommerce-native.

Filter and ordering state is represented entirely in GET parameters. The ordering form preserves filters, and WooCommerce pagination receives the sanitized filter/order arguments, so browser history restores state without JavaScript.

Faceted or non-default sorted variants are `noindex,follow`. A clean category/shop canonical is emitted only for those variants; no alternate query-string combinations are encouraged for crawling.
