# ADR-0004 — Production search technology

**Status:** Proposed — blocked by `JP-CAT-002` and `JP-DEC-009`
**Date:** 2026-09-13

## Context

Search must support product name, brand, SKU, EAN, category, need and active ingredient with accent/typo tolerance, autocomplete and configurable ranking.

## Decision

No production engine is selected yet. Compare enhanced database search, OpenSearch/Elasticsearch, Algolia and Meilisearch against approved catalog size, traffic, hosting and operational constraints.

## Consequences

Search implementation work must not hard-code a provider before the blocking inputs are approved. The domain should expose a provider-neutral search contract.
