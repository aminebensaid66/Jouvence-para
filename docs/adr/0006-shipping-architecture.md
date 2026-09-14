# ADR-0006 — Shipping architecture

**Status:** Accepted
**Date:** 2026-09-13
**Decision owner:** JP-DEC-004 / JP-GEO-002

## Decision

- Tunisia-wide home delivery uses First Delivery by default and is processed manually at launch; there is no carrier API integration.
- Home-delivery fee: 7,000 TND.
- Home delivery is free from 200,000 TND **after discounts**.
- Store pickup is free.
- Delivery estimate is two working days, excluding weekends/holidays.
- The 24 stable governorate IDs all map to the same national rule. No remote-zone or oversized surcharge is introduced.
- Shipping policy lives in `jouvence-para-core`, never the theme.

The WooCommerce shipping method calculates the free-shipping threshold from package line totals after discounts, including line tax, consistent with tax-inclusive storefront pricing. Tax treatment of the shipping charge itself remains WooCommerce/site tax configuration and is not invented by this ADR.
