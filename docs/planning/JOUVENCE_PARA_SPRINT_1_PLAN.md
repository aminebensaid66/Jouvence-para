# Jouvence Para — Sprint 1 Plan

Sprint 1 is the engineering-foundation sprint. It deliberately resolves architecture and business blockers before the team starts implementing checkout, stock side effects or shipping calculations from assumptions.

## Sprint goal

> Establish the production development foundation, freeze the critical domain decisions, create the canonical catalog model, select search architecture, and make the repository testable through CI.

## Sprint 1 tickets

| Ticket ID     | Title                                                           | Type     | Priority   | Workstream                     | Dependencies             | Blocked By / Decisions   |
|:--------------|:----------------------------------------------------------------|:---------|:-----------|:-------------------------------|:-------------------------|:-------------------------|
| JP-DEC-001    | Finalize business identity and production contacts              | Decision | P0         | Product / Client               |                          |                          |
| JP-DEC-002    | Finalize launch catalog scope and catalog ownership             | Decision | P0         | Product / Catalog              |                          |                          |
| JP-DEC-003    | Finalize inventory policy and physical-store synchronization    | Decision | P0         | Product / Operations / Backend |                          |                          |
| JP-DEC-004    | Finalize shipping carrier, zones, rates and free-shipping rules | Decision | P0         | Product / Operations           |                          |                          |
| JP-DEC-005    | Finalize COD rules and online-payment direction                 | Decision | P0         | Product / Finance / Backend    |                          |                          |
| JP-DEC-006    | Finalize return, damaged-item and refusal policy                | Decision | P0         | Product / Legal / Operations   |                          |                          |
| JP-DEC-007    | Finalize launch language strategy                               | Decision | P0         | Product / Content / Frontend   |                          |                          |
| JP-DEC-008    | Finalize promotion stacking rules                               | Decision | P0         | Product / Commerce             |                          |                          |
| JP-DEC-009    | Finalize hosting, capacity, media and recovery targets          | Decision | P0         | Tech Lead / DevOps / Product   |                          |                          |
| JP-PLAT-001   | Audit repository and establish production project structure     | Story    | P0         | Tech Lead / Backend            | JP-DEC-001               |                          |
| JP-PLAT-002   | Create `jouvence-para-core` plugin skeleton                     | Story    | P0         | Backend                        | JP-PLAT-001              |                          |
| JP-PLAT-003   | Create architecture decision record baseline                    | Task     | P0         | Tech Lead                      | JP-PLAT-001              |                          |
| JP-PLAT-004   | Establish coding standards and static checks                    | Story    | P0         | Tech Lead / DevOps             | JP-PLAT-001              |                          |
| JP-PLAT-005   | Establish test framework and fixtures                           | Story    | P0         | QA / Backend                   | JP-PLAT-001, JP-PLAT-002 |                          |
| JP-PLAT-006   | Define environment and secrets strategy                         | Story    | P0         | DevOps / Backend               | JP-PLAT-001              |                          |
| JP-CAT-001    | Implement canonical product data model                          | Story    | P0         | Backend / Catalog              | JP-DEC-002, JP-PLAT-002  |                          |
| JP-CAT-002    | Implement controlled product taxonomies and attributes          | Story    | P0         | Backend / Catalog              | JP-DEC-002, JP-CAT-001   |                          |
| JP-SEARCH-001 | Select and document production search engine                    | Decision | P0         | Tech Lead / Backend            | JP-CAT-002, JP-DEC-009   |                          |
| JP-DB-001     | Configure DB encoding, timezone and migration framework         | Story    | P0         | Backend / DevOps               | JP-PLAT-002              |                          |
| JP-CI-001     | Build CI pipeline                                               | Story    | P0         | DevOps                         | JP-PLAT-004, JP-PLAT-005 |                          |

## Suggested parallel lanes

### Product / Client / Operations
- JP-DEC-001 through JP-DEC-009, distributed to the appropriate business owners.
- These decisions should be closed early enough not to block downstream technical work.

### Tech Lead / Backend
- JP-PLAT-001 — repository structure.
- JP-PLAT-002 — `jouvence-para-core`.
- JP-PLAT-003 — ADR baseline.
- JP-CAT-001 — canonical product model.
- JP-CAT-002 — controlled taxonomy.
- JP-SEARCH-001 — search-engine ADR.
- JP-DB-001 — DB/time/migrations.

### DevOps
- JP-PLAT-004 — coding/static checks.
- JP-PLAT-006 — environments/secrets.
- JP-CI-001 — CI.

### QA
- JP-PLAT-005 — test framework/fixtures.
- Pair with Backend on deterministic sample products and first smoke tests.

## Sprint exit criteria

Sprint 1 is considered successful when:

1. A new developer can clone and run the project.
2. `jouvence-para-core` exists and owns future business logic.
3. Product field storage and controlled taxonomies are documented and implemented.
4. Search technology has an ADR/decision.
5. Stock, shipping, COD/payment, return and language decisions are either closed or visibly blocked with named owners.
6. CI runs code-quality and initial automated tests.
7. No production secret is stored in Git.
8. DB/schema migration conventions exist.
9. The team can begin catalog/search/frontend work in parallel without inventing competing domain models.

## Not recommended for Sprint 1

Do not make these the main Sprint 1 deliverables unless foundation work is already complete:

- final checkout UI;
- online payment integration;
- loyalty;
- packs;
- Arabic content entry;
- advanced recommendations;
- full production SEO tuning.

Those depend on decisions and domain foundations above.
