# Accessibility verification

The automated checks in `tests/unit/ThemeAccessibilityTest.php` protect the theme's current baseline: text and focus-indicator contrast tokens, keyboard focus visibility, primary target sizes, site-wide reduced motion, skip navigation, main landmarks, and the product-sort label. Existing integration tests cover the cart's live announcement markup and update behavior.

These repository checks do not prove WCAG 2.2 AA conformance. Before release, run the critical customer journeys with keyboard only and manually spot-check them with a screen reader (VoiceOver on macOS/iOS or TalkBack on Android). Use realistic catalog data and an installed WooCommerce runtime.

## Manual release checklist

- At 200% browser zoom, the home, catalog, product, cart, checkout, account and cookie-preference screens remain usable without horizontal scrolling for page content.
- Tab/Shift+Tab reaches every interactive control in a logical order; focus remains visible and is not hidden behind overlays.
- Product cards announce the product name, price, availability and action without duplicate image links.
- Sorting and catalog filters expose their labels, selected state and result changes.
- Cart quantity updates, removals and checkout validation errors are announced without moving focus unexpectedly.
- Checkout fields and validation errors have programmatic labels and associations.
- The cookie dialog announces its name, contains keyboard focus while open, closes on Escape, and returns focus to its opener.
- Reduced-motion preference suppresses nonessential animation and smooth scrolling.

Record the browser, assistive technology, viewport/zoom, pages reviewed, findings and follow-up issues with the release checklist. Do not record a pass for any item that was not exercised against a running WooCommerce site.
