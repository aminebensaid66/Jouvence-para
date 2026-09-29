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

## Approved native lifecycle — 2026-09-29

The business owner approved native WooCommerce reservation/decrement/cancellation
timing, retaining the 30-minute hold and one-unit buffer. REQ-STOCK-004 now uses
this decision; implementation and concurrent reservation checks belong to
JP-STOCK-002.

- Checkout-created pending/draft orders use native WooCommerce stock reservations
  and its 30-minute expiration. Cart contents alone do not reserve stock.
- Native payment-complete and processing/on-hold/completed transitions trigger
  guarded stock reduction; WooCommerce's reduced-stock records prevent replayed
  notifications from decrementing twice. COD payment collection is a separate
  payment event and must not be inferred from the processing order status.
- Before parcel departure, native cancellation/pending restoration follows the
  order/item reduction flags. Native failed-order restoration varies by supported
  WooCommerce version and must be verified on the deployed release.
- Refused/returned parcels must remain unavailable until physically received and
  inspected, then restock only eligible units under authorized operations. A
  refusal/return must not be mapped to a stock-restoring native status prematurely.
  JP-ORDER-001/JP-STOCK-002 own the enforcement; cart validation does not restore
  stock or change order status.

This approval defines stock handling only. Customer return eligibility, damaged
goods remedies and legally required reimbursements remain JP-DEC-006/JP-RET-001.
