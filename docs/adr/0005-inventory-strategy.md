# ADR-0005 — Inventory source and lifecycle

**Status:** Accepted
**Date:** 2026-09-13
**Decision owner:** JP-DEC-003 / JP-STOCK-001

## Decision

WooCommerce is the operational stock record for both online and physical-store inventory at launch. Physical sales are deducted manually by staff; POS synchronization is explicitly out of scope.

- Backorders and overselling are disabled.
- Checkout stock hold/reservation is 30 minutes.
- Low-stock threshold is 3 units.
- Customers see availability only, never exact quantities.
- One physical unit is retained as an operational safety buffer, so online availability is `max(0, physical stock - 1)`.
- Stock discrepancies are resolved manually in WooCommerce by authorized staff.

Return/restock side effects remain governed by the separate return/refusal decision and are not invented here.
