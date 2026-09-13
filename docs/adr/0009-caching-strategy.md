# ADR-0009 — Commerce-safe caching

**Status:** Proposed — blocked by `JP-DEC-009`
**Date:** 2026-09-13

## Context

The target architecture allows browser, CDN, page, object and media caching, but hosting/CDN choices and expected load are unresolved.

## Proposed direction

Never full-page-cache cart, checkout, account or order-confirmation pages. Any selected cache must support invalidation for product, price, stock and promotion changes.

## Consequences

Provider and cache implementation remain open until hosting/capacity decisions are approved.
