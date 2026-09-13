# ADR-0006 — Shipping architecture

**Status:** Proposed — blocked by `JP-DEC-004`
**Date:** 2026-09-13

## Context

Shipping depends on Tunisia geography, carrier selection, rates, free-shipping rules and delivery estimates that are not yet approved.

## Proposed direction

Represent geographic rules with stable identifiers and expose carrier integrations behind provider interfaces. Keep shipping configuration outside theme code and preserve a manual operational fallback.

## Consequences

No rate, threshold, carrier or delivery promise is approved by this ADR.
