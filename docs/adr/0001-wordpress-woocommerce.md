# ADR-0001 — WordPress and WooCommerce as commerce platform

**Status:** Accepted
**Date:** 2026-09-13

## Context

The project already uses WordPress/WooCommerce and the production specification defines WooCommerce as the initial source of truth for products, variations, prices, carts, coupons, customers, orders and base inventory.

## Decision

Use WordPress and WooCommerce as the commerce platform. Custom business behavior is implemented in `jouvence-para-core` and must use WooCommerce APIs, including HPOS-compatible order APIs.

## Alternatives considered

- Replace the commerce engine with a custom application.
- Use WooCommerce only as a catalog while duplicating commerce state elsewhere.

## Consequences

The team inherits WooCommerce conventions and upgrade responsibilities, but avoids rebuilding commodity commerce primitives. Custom code must not bypass WooCommerce APIs without an explicit ADR.
