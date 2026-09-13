# ADR-0010 — Media storage and CDN strategy

**Status:** Proposed — blocked by `JP-DEC-009`
**Date:** 2026-09-13

## Context

The catalog is image-heavy and requires responsive optimized media, but production storage/CDN infrastructure is not yet selected.

## Proposed direction

Keep original/high-quality product assets and serve responsive WebP/AVIF renditions where supported. Storage/CDN details must remain environment-configurable.

## Consequences

No production storage vendor or CDN is selected by this ADR.
