# ADR-0003 — Product taxonomy model

**Status:** Accepted
**Date:** 2026-09-13
**Decision owner:** JP-DEC-002 / JP-CAT-002

## Context

Jouvence Para launches with approximately 1,000 products. The catalog requires stable commercial categories, controlled brands and filterable vocabularies without free-text duplication. French is the launch language, but identifiers must remain independent of translated labels.

## Decision

- Keep WooCommerce `product_cat` as the hierarchical commercial category source of truth.
- Register `jp_brand` as the controlled brand taxonomy.
- Register `jp_need` as the controlled product-concern/need taxonomy.
- Use WooCommerce global select attributes for skin type, hair type, SPF, size/volume and target audience.
- Stable taxonomy/attribute slugs are machine identifiers and must not be derived again when a translated label changes.
- Seed only the approved top-level commercial categories. Brand, need and attribute terms remain staff-managed controlled values; importers must never silently create them.
- Prevent duplicate controlled brand/need slugs before insertion.

## Initial top-level categories

`visage`, `corps`, `capillaire`, `hygiene`, `protection-solaire`, `bebe-maman`, `complements-alimentaires`, `bio-naturel`, `homme`, `yeux`, `mains-pieds-levres`, `materiel-medical-orthopedique`.

## Consequences

Catalog filters and future search can address stable taxonomies instead of free text. French labels may later receive Arabic equivalents without changing stored identifiers. Taxonomy structures may use competitor sites only as market research; competitor-authored catalog content is not imported.
