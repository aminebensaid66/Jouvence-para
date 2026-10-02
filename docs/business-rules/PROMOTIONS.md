# Promotion rules

This launch policy comes from the store owner's decision recorded on GitHub issue #8.

- Native WooCommerce product sale prices represent product-level sale promotions. WooCommerce remains authoritative for current and scheduled end prices.
- Category and brand promotions are launched as targeted coupon codes: native WooCommerce category restrictions and the core-plugin brand selector restrict eligible line discounts. Automatic category-wide or brand-wide sale-price campaigns without a coupon code are outside JP-PROMO-001.
- Configure the WordPress/WooCommerce site timezone as `Africa/Tunis` before staff enter native product sale schedules or coupon expiry times; the deployment setting keeps WooCommerce's native start/end fields in the approved business timezone.
- WooCommerce coupons provide percentage, fixed-product and fixed-cart discount codes. Native product/category, minimum spend, usage-limit, email and exclusion settings remain authoritative. Free delivery is the approved shipping threshold benefit, not a free-shipping coupon code, so native free-shipping coupons are rejected server-side.
- Coupon start times entered by an administrator are interpreted in `Africa/Tunis` and stored as UTC timestamps. A coupon with a future start time is rejected server-side until that instant. WooCommerce's native expiry controls the end time and removes expired discounts without cleanup jobs.
- A coupon may be restricted to the controlled `jp_brand` taxonomy. Brand restrictions are checked server-side and discount only matching products, including variations through their parent product.
- Coupons exclude sale-priced products. Free shipping remains compatible with coupons, subject to the approved 200 TND after-discount threshold in the shipping rules.
- Bundles, gifts, BOGO offers, customer-specific pricing, packs and routines are not launch features.
- Administrators create promotions and the store owner approves them. Approval remains an operational responsibility; this patch does not add an approval workflow or change user roles.

Promotion stacking and valid reference-price display are owned by JP-PROMO-002. This implementation does not define additional combinations. The approval and launch policy above is not legal advice about reference pricing; the storefront must not display an original/strikethrough price until the approved JP-PROMO-002 rules are implemented.
