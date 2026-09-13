# ADR-0002 — Separate presentation from business logic

**Status:** Accepted
**Date:** 2026-09-13

## Context

The project needs a replaceable presentation layer without coupling stock, checkout, order, shipping or payment rules to the active theme.

## Decision

The `jouvence-para` theme owns templates, styles and frontend presentation. The `jouvence-para-core` plugin owns business rules, domain modules, WooCommerce hooks, validation, integrations, jobs and custom administration behavior.

## Alternatives considered

- Put all custom behavior in the theme.
- Create many small feature plugins immediately.

## Consequences

Theme replacement remains possible without losing commerce rules. Cross-theme business behavior requires explicit plugin contracts rather than theme functions.
