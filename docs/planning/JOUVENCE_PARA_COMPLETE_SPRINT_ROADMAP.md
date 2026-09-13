# Jouvence Para — Complete Sprint Roadmap

This roadmap is derived from `JOUVENCE_PARA_BACKLOG.csv` and the production requirements. It is a dependency-aware planning baseline, not a commitment to fixed calendar duration. Actual sprint load should be adjusted to team capacity without breaking dependencies or acceptance criteria.

## Sprint 1

**Goal:** Establish engineering foundations, resolve critical decisions, freeze the canonical catalog model, select search architecture, and make CI/testing operational.

**Direct tickets:** 20

| Ticket ID     | Title                                                           | Priority   | Workstream                     |
|:--------------|:----------------------------------------------------------------|:-----------|:-------------------------------|
| JP-DEC-001    | Finalize business identity and production contacts              | P0         | Product / Client               |
| JP-DEC-002    | Finalize launch catalog scope and catalog ownership             | P0         | Product / Catalog              |
| JP-DEC-003    | Finalize inventory policy and physical-store synchronization    | P0         | Product / Operations / Backend |
| JP-DEC-004    | Finalize shipping carrier, zones, rates and free-shipping rules | P0         | Product / Operations           |
| JP-DEC-005    | Finalize COD rules and online-payment direction                 | P0         | Product / Finance / Backend    |
| JP-DEC-006    | Finalize return, damaged-item and refusal policy                | P0         | Product / Legal / Operations   |
| JP-DEC-007    | Finalize launch language strategy                               | P0         | Product / Content / Frontend   |
| JP-DEC-008    | Finalize promotion stacking rules                               | P0         | Product / Commerce             |
| JP-DEC-009    | Finalize hosting, capacity, media and recovery targets          | P0         | Tech Lead / DevOps / Product   |
| JP-PLAT-001   | Audit repository and establish production project structure     | P0         | Tech Lead / Backend            |
| JP-PLAT-002   | Create `jouvence-para-core` plugin skeleton                     | P0         | Backend                        |
| JP-PLAT-003   | Create architecture decision record baseline                    | P0         | Tech Lead                      |
| JP-PLAT-004   | Establish coding standards and static checks                    | P0         | Tech Lead / DevOps             |
| JP-PLAT-005   | Establish test framework and fixtures                           | P0         | QA / Backend                   |
| JP-PLAT-006   | Define environment and secrets strategy                         | P0         | DevOps / Backend               |
| JP-CAT-001    | Implement canonical product data model                          | P0         | Backend / Catalog              |
| JP-CAT-002    | Implement controlled product taxonomies and attributes          | P0         | Backend / Catalog              |
| JP-SEARCH-001 | Select and document production search engine                    | P0         | Tech Lead / Backend            |
| JP-DB-001     | Configure DB encoding, timezone and migration framework         | P0         | Backend / DevOps               |
| JP-CI-001     | Build CI pipeline                                               | P0         | DevOps                         |

## Sprint 2

**Goal:** Stabilize the catalog, product-content, email and security foundations so product discovery can be built on real, controlled data.

**Direct tickets:** 13

| Ticket ID     | Title                                                          | Priority   | Workstream                   |
|:--------------|:---------------------------------------------------------------|:-----------|:-----------------------------|
| JP-CAT-003    | Build controlled CSV import/export workflow                    | P0         | Backend / Catalog            |
| JP-CAT-004    | Implement media pipeline and product image standards           | P0         | Frontend / Backend           |
| JP-CAT-005    | Implement product administration and validation                | P0         | Backend / Catalog            |
| JP-SEARCH-002 | Implement search indexing, normalization and ranking           | P0         | Backend                      |
| JP-DISC-001   | Build responsive category/product grid                         | P0         | Frontend                     |
| JP-PDP-004    | Enforce product content and medical-claim rules                | P0         | Catalog / Backend            |
| JP-STOCK-001  | Configure inventory source, overselling and low-stock behavior | P0         | Backend / Operations         |
| JP-I18N-001   | Implement French baseline and Arabic-ready architecture        | P0         | Frontend / Backend / Content |
| JP-ADMIN-002  | Implement least-privilege staff roles                          | P0         | Backend / Security           |
| JP-EMAIL-001  | Configure reliable transactional email delivery and DNS        | P0         | DevOps / Backend             |
| JP-SEC-002    | Implement admin 2FA, least privilege and WordPress hardening   | P0         | Backend / DevOps / Security  |
| JP-SEC-004    | Implement dependency vulnerability monitoring                  | P0         | DevOps / Security            |
| JP-OBS-001    | Implement application error tracking, logs and core metrics    | P0         | DevOps / Backend             |

## Sprint 3

**Goal:** Deliver the first strong customer discovery experience: search suggestions, filters, product details, WhatsApp assistance, merchandising controls, consent, and deployable staging.

**Direct tickets:** 12

| Ticket ID     | Title                                                             | Priority   | Workstream                     |
|:--------------|:------------------------------------------------------------------|:-----------|:-------------------------------|
| JP-SEARCH-003 | Implement search autocomplete UI and endpoint                     | P0         | Frontend / Backend             |
| JP-SEARCH-004 | Implement zero-result recovery and search analytics               | P0         | Frontend / Backend / Analytics |
| JP-DISC-002   | Implement faceted filters and category-aware availability         | P0         | Frontend / Backend             |
| JP-DISC-003   | Implement sorting, pagination/loading and filter URLs             | P0         | Frontend / Backend / SEO       |
| JP-PDP-001    | Build complete product detail template                            | P0         | Frontend / Backend             |
| JP-PDP-002    | Implement delivery visibility and contextual WhatsApp advice      | P0         | Frontend / Backend             |
| JP-WA-001     | Implement global and product-context WhatsApp                     | P0         | Frontend / Backend             |
| JP-ADMIN-001  | Implement homepage merchandising controls                         | P0         | Backend / Frontend Admin       |
| JP-AUDIT-001  | Implement sensitive-operation audit log                           | P0         | Backend / Security             |
| JP-COOKIE-001 | Implement cookie consent and preference controls                  | P0         | Frontend / Backend / Legal     |
| JP-CD-001     | Build staging deployment, production deployment and rollback flow | P0         | DevOps                         |
| JP-BACKUP-001 | Implement encrypted off-server backups                            | P0         | DevOps                         |

## Sprint 4

**Goal:** Build the core commerce foundation: cart, promotions, payment abstraction, account basics, order state model, analytics/SEO foundation, webhooks and background jobs.

**Direct tickets:** 16

| Ticket ID      | Title                                                         | Priority   | Workstream                     |
|:---------------|:--------------------------------------------------------------|:-----------|:-------------------------------|
| JP-PDP-003     | Implement related/complementary products and product schema   | P0         | Backend / Frontend / SEO       |
| JP-CART-001    | Implement cart operations and persistent cart                 | P0         | Frontend / Backend             |
| JP-CART-002    | Revalidate cart price and stock at critical points            | P0         | Backend / Frontend             |
| JP-CART-003    | Implement coupons, free-shipping progress and limited upsells | P0         | Frontend / Backend             |
| JP-PROMO-001   | Implement promotion types, scheduling and eligibility         | P0         | Backend / Commerce             |
| JP-PROMO-002   | Implement stacking policy and valid reference-price display   | P0         | Backend / Frontend             |
| JP-GEO-001     | Implement Tunisia geography data model                        | P0         | Backend / Frontend             |
| JP-GEO-002     | Implement shipping matrix configuration                       | P0         | Backend                        |
| JP-PAY-002     | Implement payment abstraction and safe payment data model     | P0         | Backend                        |
| JP-ORDER-001   | Implement order state machine and controlled transitions      | P0         | Backend / Operations           |
| JP-ACC-001     | Implement authentication and account profile                  | P0         | Frontend / Backend             |
| JP-WEBHOOK-001 | Build reusable signed/idempotent webhook processing framework | P0         | Backend / Integration          |
| JP-JOBS-001    | Implement background-job conventions and failure visibility   | P0         | Backend / DevOps               |
| JP-SEO-001     | Implement SEO URL, metadata and structured-data foundation    | P0         | Frontend / Backend / SEO       |
| JP-AN-001      | Implement analytics event contract                            | P0         | Frontend / Backend / Analytics |
| JP-EMAIL-002   | Build branded transactional email templates                   | P0         | Frontend / Backend             |

## Sprint 5

**Goal:** Complete the production ordering path: checkout, Tunisia shipping, COD, order side effects, stock lifecycle, shipment administration, order confirmation, security and alerting.

**Direct tickets:** 13

| Ticket ID       | Title                                                       | Priority   | Workstream               |
|:----------------|:------------------------------------------------------------|:-----------|:-------------------------|
| JP-STOCK-002    | Implement reservation, decrement and restoration lifecycle  | P0         | Backend                  |
| JP-STOCK-003    | Implement inventory audit trail                             | P0         | Backend                  |
| JP-CHECKOUT-001 | Build guest checkout fields and server-side validation      | P0         | Frontend / Backend       |
| JP-CHECKOUT-002 | Implement shipping cost, terms and final order summary      | P0         | Frontend / Backend       |
| JP-CHECKOUT-003 | Implement idempotent order submission and checkout recovery | P0         | Backend / Frontend       |
| JP-SHIP-001     | Implement shipment domain model and admin data              | P0         | Backend / Operations     |
| JP-PAY-001      | Implement COD payment method and rules                      | P0         | Backend / Frontend       |
| JP-ORDER-002    | Implement order transition side effects                     | P0         | Backend                  |
| JP-ORDER-003    | Implement order notes, history and operational admin view   | P0         | Backend / Frontend Admin |
| JP-ORDER-004    | Build order confirmation page and transactional email       | P0         | Frontend / Backend       |
| JP-SEO-002      | Implement faceted crawl policy and removed-product behavior | P0         | Backend / SEO            |
| JP-SEC-001      | Configure TLS, production headers and secure cookies        | P0         | DevOps / Security        |
| JP-OBS-002      | Configure operational alerts                                | P0         | DevOps                   |

## Sprint 6

**Goal:** Finish operational workflows and production hardening: tracking, returns/SAV, account order tools, privacy, accessibility, caching, performance diagnostics and critical E2E coverage.

**Direct tickets:** 12

| Ticket ID    | Title                                                            | Priority   | Workstream                       |
|:-------------|:-----------------------------------------------------------------|:-----------|:---------------------------------|
| JP-SHIP-002  | Expose customer tracking and carrier integration fallback        | P0         | Backend / Frontend / Integration |
| JP-RET-001   | Implement structured return request workflow                     | P0         | Backend / Frontend / Operations  |
| JP-SAV-001   | Implement complaint/SAV form with secure attachments             | P0         | Backend / Frontend / Operations  |
| JP-ACC-002   | Implement customer order history, tracking and reorder           | P0         | Frontend / Backend               |
| JP-ACC-003   | Enforce object-level authorization and data rights workflows     | P0         | Backend / Security               |
| JP-MKT-001   | Implement newsletter consent and unsubscribe workflow            | P0         | Frontend / Backend               |
| JP-A11Y-001  | Implement accessibility baseline and automated checks            | P0         | Frontend / QA                    |
| JP-PRIV-001  | Implement privacy/data-retention operational hooks               | P0         | Backend / Product / Legal        |
| JP-DB-002    | Implement DB indexing review and slow-query diagnostics          | P0         | Backend / DevOps                 |
| JP-CACHE-001 | Implement commerce-safe caching architecture                     | P0         | DevOps / Backend                 |
| JP-PERF-001  | Implement frontend performance budget and Core Web Vitals checks | P0         | Frontend / DevOps / QA           |
| JP-QA-001    | Automate critical E2E commerce journeys                          | P0         | QA / Frontend / Backend          |

## Pre-launch

**Goal:** Validate the real production system and data under release conditions, prove recovery, run pilot orders, and make the formal GO/NO-GO decision.

**Direct tickets:** 5

| Ticket ID     | Title                                                          | Priority   | Workstream                            |
|:--------------|:---------------------------------------------------------------|:-----------|:--------------------------------------|
| JP-PERF-002   | Run backend/load performance testing against approved capacity | P0         | Backend / DevOps / QA                 |
| JP-BACKUP-002 | Test restoration and RPO/RTO runbook                           | P0         | DevOps / QA                           |
| JP-QA-002     | Run supported browser/device release matrix                    | P0         | QA / Frontend                         |
| JP-DATA-001   | Remove demo data and migrate validated production catalog      | P0         | Catalog / Backend / QA                |
| JP-LAUNCH-001 | Execute production GO/NO-GO checklist and pilot orders         | P0         | Tech Lead / Product / QA / Operations |

## Post-launch P1

**Goal:** Activate commercial-growth features on top of stable production operations without changing the core architecture.

**Direct tickets:** 7

| Ticket ID    | Title                                                | Priority   | Workstream                       |
|:-------------|:-----------------------------------------------------|:-----------|:---------------------------------|
| JP-STOCK-005 | Implement back-in-stock subscriptions                | P1         | Backend / Frontend               |
| JP-PROMO-003 | Implement packs/routines                             | P1         | Backend / Frontend               |
| JP-PAY-003   | Integrate online payment provider                    | P1         | Backend / Frontend / Integration |
| JP-ORDER-005 | Implement transactional SMS/WhatsApp order messaging | P1         | Backend / Integration            |
| JP-WISH-001  | Implement account wishlist                           | P1         | Frontend / Backend               |
| JP-REV-001   | Implement moderated verified-purchase reviews        | P1         | Backend / Frontend               |
| JP-WA-002    | Implement safe cart sharing to WhatsApp              | P1         | Frontend / Backend               |

## P2

**Goal:** Implement advanced loyalty only after the business rules and post-launch commercial data justify it.

**Direct tickets:** 1

| Ticket ID    | Title                    | Priority   | Workstream         |
|:-------------|:-------------------------|:-----------|:-------------------|
| JP-PROMO-004 | Implement loyalty ledger | P2         | Backend / Frontend |

## Conditional backlog item

| Ticket ID    | Title                                                      | Priority   | Workstream            | Blocked By / Decisions                               |
|:-------------|:-----------------------------------------------------------|:-----------|:----------------------|:-----------------------------------------------------|
| JP-STOCK-004 | Implement physical-store stock synchronization if required | P0         | Backend / Integration | Only required if stock is shared with physical store |

This conditional item should be scheduled only if the business confirms the relevant condition (for example shared physical-store inventory).

## Continuous requirement

| Ticket ID   | Title                               | Priority   | Workstream                    |
|:------------|:------------------------------------|:-----------|:------------------------------|
| JP-SEC-003  | Apply custom-code security controls | P0         | Backend / Frontend / Security |

The continuous security ticket applies to every sprint that changes custom code; it is not a one-time security phase.
