# ADR-0007 — Payment abstraction

**Status:** Proposed — COD launch direction accepted; provider details blocked by `JP-DEC-005`
**Date:** 2026-09-13

## Context

Order state and payment state must remain independent. Cash on delivery is the current launch direction; online provider choice and COD restrictions are unresolved.

## Proposed direction

Use WooCommerce payment-method APIs with provider adapters. Persist provider transaction identifiers/statuses without storing raw card data.

## Consequences

The model can support COD and future online payment, but no provider-specific code should be added before approval.
