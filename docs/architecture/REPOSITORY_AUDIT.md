# Repository Audit — JP-PLAT-001

## Scope

This audit records the production ownership of the repository as it exists at the start of Sprint 1. It does not approve unresolved commercial decisions.

## Source ownership

| Area | Classification | Owner / rule |
|---|---|---|
| `wordpress/wp-content/themes/jouvence-para/` | Keep and evolve | Presentation, templates, CSS and frontend interaction only |
| `wordpress/wp-content/plugins/jouvence-para-core/` | Keep and evolve | Business rules, WooCommerce hooks, validation, integrations and admin behavior |
| `docs/requirements/` | Keep | Product and technical requirements; source of truth for implementation |
| `docs/planning/` | Keep | Sprint sequencing and ticket traceability |
| `docs/architecture/` | Keep and evolve | Runtime boundaries and technical architecture |
| `docs/adr/` | Keep and evolve | Architecture decisions and proposed decisions |
| `docs/business-rules/` | Keep and evolve | Approved rules for stock, shipping, payments, returns and promotions |
| `docs/operations/` | Keep and evolve | Staff-facing operational procedures |
| `docs/runbooks/` | Keep and evolve | Incident, deployment and recovery procedures |
| `docs/security/` | Establish | Security, environment and secret-handling documentation |
| `docs/testing/` | Establish | Test strategy and execution guidance |
| `scripts/` | Establish | Repeatable repository checks and development utilities |
| `tests/` | Establish | Unit, integration and end-to-end test suites |
| `docker/` | Establish | Docker-specific documentation/configuration when custom images are needed |
| `patches/` | Keep | Local patch workflow only; incoming/rejected patch files remain untracked |

## Existing prototype/theme classification

The current `jouvence-para` theme is retained as the presentation baseline. It must be refactored incrementally rather than replaced wholesale. Any business-critical logic discovered in the theme must be moved to `jouvence-para-core` in the ticket that owns that behavior.

The current core plugin is retained as the business-logic boundary. New domain modules belong there and must remain usable independently of the active theme.

## Repository hygiene

- Runtime WordPress core, uploads, caches, backups and database dumps are not versioned.
- `.env` and environment-specific secret files are ignored; only `.env.example` is tracked.
- Production credentials, tokens, customer data and provider secrets are prohibited from Git.
- Generated dependencies such as `vendor/` and `node_modules/` are not versioned.

## Current gaps after this audit

The directory structure is now explicit, but later Sprint 1 tickets still need to establish:

- plugin lifecycle and service registration (`JP-PLAT-002`);
- ADR baseline (`JP-PLAT-003`);
- executable coding checks (`JP-PLAT-004`);
- test harnesses (`JP-PLAT-005`);
- environment/secrets policy (`JP-PLAT-006`);
- database/migration conventions (`JP-DB-001`);
- CI pipeline (`JP-CI-001`).

Commercial decisions under `JP-DEC-001` through `JP-DEC-009` remain separate and must not be inferred from this audit.
