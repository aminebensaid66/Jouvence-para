# ADR-0008 — Multilingual strategy

**Status:** Accepted for launch
**Date:** 2026-09-13
**Decision owner:** JP-DEC-007 / JP-I18N-001

## Decision

Jouvence Para launches entirely in French (`fr_FR`): storefront, WordPress-facing custom UI, product content, checkout, legal pages and transactional copy. UI strings use WordPress gettext domains. Product/taxonomy machine identifiers are stable and language-independent.

Arabic content and an Arabic storefront are deferred. No multilingual plugin is introduced before there is translated content to serve. Future content translation must be integrated behind WordPress/WooCommerce public APIs without changing canonical product IDs, SKUs, taxonomy slugs used as internal identifiers, or business-rule keys.

The theme uses semantic HTML, logical CSS properties and WordPress `language_attributes()` so an Arabic activation can use native `dir="rtl"`. New CSS must avoid directional assumptions when a logical property exists.

## URL and hreflang rule

At launch there is one French URL per canonical resource and no fake alternate `hreflang`. When a second locale is activated, translated URLs may differ for presentation/SEO, but stable internal identifiers remain unchanged. `hreflang` is emitted only when a real translated counterpart exists and includes a self-reference.

## Time

Business scheduling remains `Africa/Tunis`; locale formatting must not change persisted UTC event timestamps.
