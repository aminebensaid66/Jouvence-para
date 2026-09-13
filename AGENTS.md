# Jouvence Para Repository Instructions

These instructions apply to every AI-assisted change in this repository.

## Authoritative sources

Read these before implementing a ticket:

1. `docs/requirements/JOUVENCE_PARA_PRODUCTION_REQUIREMENTS.md`
2. The relevant sprint plan under `docs/planning/`
3. `docs/architecture/ARCHITECTURE.md`
4. `docs/workflow/PATCH_WORKFLOW.md`

If sources conflict, stop and report the conflict. Do not invent rules affecting money, stock, orders, shipping, payments, privacy, customer rights, or health content.

## Change scope

- Implement one ticket per patch unless the request explicitly groups tightly coupled tickets.
- Reference the ticket ID in the patch filename and proposed commit message.
- Do not make unrelated formatting, renaming, dependency, or architecture changes.
- Preserve existing behavior unless the ticket requires changing it.
- Do not edit generated files, dependencies, uploads, secrets, or runtime data.
- Do not add a plugin when a small internal module is sufficient.
- Do not put business-critical logic in the theme.

## Architecture boundaries

- `wordpress/wp-content/themes/jouvence-para/`: templates, visual components, styles, frontend presentation behavior.
- `wordpress/wp-content/plugins/jouvence-para-core/`: business rules, domain modules, WooCommerce hooks, validation, integrations, jobs, audit behavior, custom admin behavior.
- WooCommerce remains the source of truth for products, variations, prices, carts, coupons, customers, orders, and base stock.
- External providers must be accessed through interfaces/adapters. Domain logic must not depend directly on provider-specific APIs.
- Cross-module communication should use explicit services, value objects, or documented WordPress/WooCommerce hooks.

## PHP rules

- Target the PHP and WordPress/WooCommerce versions documented by the project.
- Follow WordPress Coding Standards.
- Use the `JouvencePara` PHP namespace for new object-oriented core-plugin code.
- Use `jp_` only where WordPress requires globally named functions/options/hooks.
- Validate and sanitize input, authorize the action, verify nonces, and escape at output.
- Use WooCommerce APIs instead of direct database writes when an API exists.
- All state-changing handlers must define duplicate/retry behavior.
- Never log secrets, tokens, passwords, raw payment data, or unnecessary personal data.

## Frontend rules

- Mobile-first and progressively enhanced.
- Meet WCAG 2.2 AA expectations relevant to the changed component.
- Support keyboard navigation, visible focus, reduced motion, field labels, and accessible dynamic announcements.
- Do not introduce a new styling system outside the project design tokens/components.
- Keep third-party scripts and frontend dependencies minimal.

## Data and migrations

- Schema changes belong to versioned, repeatable plugin migrations.
- Migrations must be safe to run more than once and safe during upgrades.
- Stable identifiers must not depend on translated labels.
- Filterable values must use controlled taxonomies/attributes, not arbitrary free text.
- Never silently create a taxonomy term during production import unless an approved rule explicitly allows it.

## Tests and verification

Every patch must include tests proportional to the risk. Before delivery:

1. run relevant lint/static checks;
2. run relevant unit/integration tests;
3. run or document a focused manual check;
4. inspect `git diff --check`;
5. report every command and result honestly.

Do not claim a check passed if it was not executed.

## Required response format

For implementation requests, produce a downloadable plain-text unified diff named:

```text
JP-<DOMAIN>-<NUMBER>-short-description.patch
```

The patch must:

- be generated relative to repository root;
- use `diff --git`, `---`, `+++`, and complete hunks;
- contain no Markdown fences or commentary;
- contain no binary data, secrets, runtime data, or unrelated changes;
- apply with `git apply --check` against the base commit supplied in the request.

Alongside the downloadable patch, provide a short summary containing:

- ticket implemented;
- files changed;
- important decisions/assumptions;
- tests run and their results;
- proposed commit message;
- blockers or follow-up work.

Never say a patch applies cleanly unless it was validated against the exact supplied repository snapshot/base commit.
