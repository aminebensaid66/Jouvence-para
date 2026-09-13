# ADR-0003 — Product taxonomy model

**Status:** Proposed — blocked by `JP-DEC-002`
**Date:** 2026-09-13

## Context

The product specification distinguishes commercial category, brand, need and controlled attributes. Final launch categories, brands and catalog ownership are not yet approved.

## Proposed direction

Keep WooCommerce `product_cat` for commercial categories and model brand, need and filterable controlled vocabularies through stable taxonomies/attributes registered by `jouvence-para-core`.

## Alternatives considered

- Free-text product metadata for filters.
- Duplicate product pages per need/category.

## Consequences

The direction supports deterministic filtering and future translation, but no final taxonomy names or seeded terms are approved until `JP-DEC-002` is complete.
