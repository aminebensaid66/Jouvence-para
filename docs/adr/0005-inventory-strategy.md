# ADR-0005 — Inventory source and lifecycle

**Status:** Proposed — blocked by `JP-DEC-003`
**Date:** 2026-09-13

## Context

WooCommerce is the initial inventory source unless a shared physical-store/POS source is approved. Reservation, decrement, cancellation and return behavior are business-critical and unresolved.

## Decision

Use WooCommerce base stock until `JP-DEC-003` approves another source. Do not implement reservation/restoration side effects until the business lifecycle is documented.

## Consequences

Catalog work can use WooCommerce stock fields, while checkout/order stock side effects remain blocked.
