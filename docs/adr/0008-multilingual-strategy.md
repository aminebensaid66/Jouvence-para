# ADR-0008 — Multilingual strategy

**Status:** Proposed — blocked by `JP-DEC-007`
**Date:** 2026-09-13

## Context

French is the current primary language direction and the architecture must remain Arabic/RTL-ready. The launch language scope and translation ownership are unresolved.

## Decision

Do not select a multilingual plugin or translated-URL strategy until the launch language decision is approved. New custom data structures must use stable identifiers independent of translated labels.

## Consequences

Sprint 1 code must avoid assumptions that prevent later Arabic labels or RTL rendering.
