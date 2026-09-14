# Product detail presentation — JP-PDP-001

The theme owns the page shell while WooCommerce public hooks own gallery, title, prices, excerpt, stock-aware cart controls, native variation form, meta, tabs and related products. This preserves WooCommerce variation price/purchasability behavior and prevents a second commerce implementation.

The controlled brand is rendered before the title. The existing media pipeline and WooCommerce gallery sizes handle optimized responsive images. Optional WooCommerce sections are emitted only when their hooks have content. Exact stock quantities remain suppressed by JP-STOCK-001.
