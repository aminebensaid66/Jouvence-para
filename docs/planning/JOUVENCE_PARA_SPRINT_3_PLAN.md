# Jouvence Para — Sprint 3 Plan

## Sprint goal

> Deliver the first strong customer discovery experience: search suggestions, filters, product details, WhatsApp assistance, merchandising controls, consent, and deployable staging.

## Entry criteria

- Catalog schema/taxonomy is stable enough for UI work.
- Search engine decision is closed.
- Production identity/WhatsApp and shipping information needed by product-page messaging are available.
- Staging and CI baseline are functioning.

## Assigned tickets

| Ticket ID     | Title                                                             | Type   | Priority   | Workstream                     | Dependencies                       | Blocked By / Decisions   |
|:--------------|:------------------------------------------------------------------|:-------|:-----------|:-------------------------------|:-----------------------------------|:-------------------------|
| JP-SEARCH-003 | Implement search autocomplete UI and endpoint                     | Story  | P0         | Frontend / Backend             | JP-SEARCH-002                      |                          |
| JP-SEARCH-004 | Implement zero-result recovery and search analytics               | Story  | P0         | Frontend / Backend / Analytics | JP-SEARCH-002                      |                          |
| JP-DISC-002   | Implement faceted filters and category-aware availability         | Story  | P0         | Frontend / Backend             | JP-CAT-002                         |                          |
| JP-DISC-003   | Implement sorting, pagination/loading and filter URLs             | Story  | P0         | Frontend / Backend / SEO       | JP-DISC-002                        |                          |
| JP-PDP-001    | Build complete product detail template                            | Story  | P0         | Frontend / Backend             | JP-CAT-001, JP-CAT-004             |                          |
| JP-PDP-002    | Implement delivery visibility and contextual WhatsApp advice      | Story  | P0         | Frontend / Backend             | JP-PDP-001, JP-DEC-001, JP-DEC-004 |                          |
| JP-WA-001     | Implement global and product-context WhatsApp                     | Story  | P0         | Frontend / Backend             | JP-DEC-001, JP-PDP-002             |                          |
| JP-ADMIN-001  | Implement homepage merchandising controls                         | Story  | P0         | Backend / Frontend Admin       | JP-PLAT-002                        |                          |
| JP-AUDIT-001  | Implement sensitive-operation audit log                           | Story  | P0         | Backend / Security             | JP-PLAT-002                        |                          |
| JP-COOKIE-001 | Implement cookie consent and preference controls                  | Story  | P0         | Frontend / Backend / Legal     |                                    |                          |
| JP-CD-001     | Build staging deployment, production deployment and rollback flow | Story  | P0         | DevOps                         | JP-CI-001, JP-DEC-009              |                          |
| JP-BACKUP-001 | Implement encrypted off-server backups                            | Story  | P0         | DevOps                         | JP-DEC-009                         |                          |

## Continuous engineering control carried through this sprint

| Ticket ID   | Title                               | Type   | Priority   | Workstream                    | Dependencies   | Blocked By / Decisions   |
|:------------|:------------------------------------|:-------|:-----------|:------------------------------|:---------------|:-------------------------|
| JP-SEC-003  | Apply custom-code security controls | Story  | P0         | Backend / Frontend / Security | JP-PLAT-002    |                          |

## Suggested parallel lanes

### Search / Backend
- **JP-SEARCH-003** — Implement search autocomplete UI and endpoint
- **JP-SEARCH-004** — Implement zero-result recovery and search analytics
- **JP-DISC-002** — Implement faceted filters and category-aware availability
- **JP-DISC-003** — Implement sorting, pagination/loading and filter URLs

### Frontend
- **JP-SEARCH-003** — Implement search autocomplete UI and endpoint
- **JP-DISC-002** — Implement faceted filters and category-aware availability
- **JP-DISC-003** — Implement sorting, pagination/loading and filter URLs
- **JP-PDP-001** — Build complete product detail template
- **JP-PDP-002** — Implement delivery visibility and contextual WhatsApp advice
- **JP-WA-001** — Implement global and product-context WhatsApp

### Backend / Admin
- **JP-ADMIN-001** — Implement homepage merchandising controls
- **JP-AUDIT-001** — Implement sensitive-operation audit log

### DevOps / Privacy
- **JP-COOKIE-001** — Implement cookie consent and preference controls
- **JP-CD-001** — Build staging deployment, production deployment and rollback flow
- **JP-BACKUP-001** — Implement encrypted off-server backups

## Exit criteria

- Customers can discover products through autocomplete and faceted catalog navigation.
- Product pages render complete canonical data.
- Product-context WhatsApp uses real configured contact information.
- Homepage merchandising is manageable without code.
- Cookie preferences control non-essential tracking.
- Staging deployment and rollback workflow exist.
- Backups are configured.
- Custom-code security controls remain enforced for all changes in the sprint.

## Dependency / blocker handling

A ticket must not be implemented by inventing an unresolved business rule. If its `Blocked By / Decisions` field is unresolved, the team may implement only the non-dependent foundation and must keep the behavior disabled or incomplete until the decision is approved.

## Ticket details

### JP-SEARCH-003 — Implement search autocomplete UI and endpoint
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend
**Requirements:** REQ-SEARCH-003
**Dependencies:** JP-SEARCH-002
**Blocked by / decisions:** None

Provide accessible, fast suggestions as the user types.

**Acceptance criteria**

- Bounded suggestions return product image/name/brand/price/stock when available.
- Keyboard navigation works.
- Screen-reader semantics are valid.
- Slow/failed search service does not freeze the page.
- Requests are debounced/cancelled appropriately.

### JP-SEARCH-004 — Implement zero-result recovery and search analytics
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend / Analytics
**Requirements:** REQ-SEARCH-005, REQ-SEARCH-006
**Dependencies:** JP-SEARCH-002
**Blocked by / decisions:** None

Turn failed searches into useful recovery paths and measurable data.

**Acceptance criteria**

- Original query remains visible.
- Correction/alternatives are shown where possible.
- Useful categories/popular products or contact path are shown.
- `search_no_results` is emitted with no unnecessary PII.
- Search result clicks can be attributed to the originating query.

### JP-DISC-002 — Implement faceted filters and category-aware availability
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend
**Requirements:** REQ-FILTER-002, REQ-FILTER-003, REQ-FILTER-004
**Dependencies:** JP-CAT-002
**Blocked by / decisions:** None

Provide deterministic filter behavior based on controlled catalog fields.

**Acceptance criteria**

- Only relevant filter groups are shown for a category.
- Values within a group use OR; separate groups use AND.
- Active filters and result count are visible.
- Single filter removal and clear-all work without stale state.
- Filter counts/results remain consistent.

**Tests**

- Multi-brand OR.
- Brand + need AND.
- Clear one/all.
- Zero-result combination.

### JP-DISC-003 — Implement sorting, pagination/loading and filter URLs
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend / SEO
**Requirements:** REQ-FILTER-005, REQ-FILTER-006, REQ-SEO-004
**Dependencies:** JP-DISC-002
**Blocked by / decisions:** None

Make listing state shareable and SEO-safe.

**Acceptance criteria**

- Relevance, bestseller, newest and price sorts work deterministically.
- Pagination/progressive loading preserves addressable URLs.
- Back/forward navigation restores state.
- Low-value filter combinations follow noindex/canonical policy.
- No uncontrolled crawl explosion is introduced.

### JP-PDP-001 — Build complete product detail template
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend
**Requirements:** REQ-PRODUCT-001, REQ-PRODUCT-002
**Dependencies:** JP-CAT-001, JP-CAT-004
**Blocked by / decisions:** None

Render complete, responsive product information from canonical data.

**Acceptance criteria**

- All available required core product fields render correctly.
- Price/sale/stock/quantity state is accurate.
- Gallery is optimized, accessible and responsive.
- Missing optional data does not create empty/broken sections.
- Variation selection, where present, updates purchasable state correctly.

### JP-PDP-002 — Implement delivery visibility and contextual WhatsApp advice
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend
**Requirements:** REQ-PRODUCT-003, REQ-PRODUCT-004
**Dependencies:** JP-PDP-001, JP-DEC-001, JP-DEC-004
**Blocked by / decisions:** None

Expose delivery information and a contextual human-advice path.

**Acceptance criteria**

- Delivery information or clear shipping link is visible.
- WhatsApp action uses the real configured number.
- Prefilled message contains product name and canonical URL.
- Message is never auto-sent.
- Action is tracked without leaking message content.

### JP-WA-001 — Implement global and product-context WhatsApp
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend
**Requirements:** REQ-WA-001, REQ-WA-002, REQ-WA-004
**Dependencies:** JP-DEC-001, JP-PDP-002
**Blocked by / decisions:** None

Use production contact data and response-hours text for a non-intrusive WhatsApp entry point.

**Acceptance criteria**

- Global entry point uses configured real number.
- Product action pre-fills product name and canonical URL.
- Hours/response expectation copy is configurable.
- No message auto-send occurs.

### JP-ADMIN-001 — Implement homepage merchandising controls
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Frontend Admin
**Requirements:** REQ-ADMIN-004
**Dependencies:** JP-PLAT-002
**Blocked by / decisions:** None

Allow authorized staff to manage banners/featured blocks without code deployment.

**Acceptance criteria**

- Banners and featured category/brand/product blocks are manageable.
- Input validation prevents broken layouts.
- Changes are previewable/reversible through normal content workflow.

### JP-AUDIT-001 — Implement sensitive-operation audit log
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Security
**Requirements:** REQ-AUDIT-001, REQ-AUDIT-002
**Dependencies:** JP-PLAT-002
**Blocked by / decisions:** None

Record security/commerce-sensitive admin changes without logging secrets.

**Acceptance criteria**

- Required price/stock/promo/order/refund/customer/coupon/role/shipping/payment changes are logged.
- Actor/action/object/timestamp/environment are present.
- Old/new values are included where feasible.
- Secrets/raw payment data are excluded.

### JP-COOKIE-001 — Implement cookie consent and preference controls
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend / Legal
**Requirements:** REQ-COOKIE-001, REQ-COOKIE-002, REQ-COOKIE-003
**Dependencies:** None
**Blocked by / decisions:** None

Block non-essential trackers until required consent while preserving commerce functionality.

**Acceptance criteria**

- Non-essential analytics/marketing scripts do not execute before required consent.
- Preferences can be reviewed/changed.
- Refusing consent does not break catalog/cart/checkout/account/order.

### JP-CD-001 — Build staging deployment, production deployment and rollback flow
**Type:** Story
**Priority:** P0
**Workstream:** DevOps
**Requirements:** Architecture / operational requirement
**Dependencies:** JP-CI-001, JP-DEC-009
**Blocked by / decisions:** None

Make deployments reproducible and production changes recoverable.

**Acceptance criteria**

- Staging deployment is automated/repeatable.
- Production deployment has explicit approval gate.
- Pre-deploy checks/backup step exists.
- Rollback/runbook exists.
- Post-deploy smoke tests run.

### JP-BACKUP-001 — Implement encrypted off-server backups
**Type:** Story
**Priority:** P0
**Workstream:** DevOps
**Requirements:** REQ-BACKUP-001, REQ-BACKUP-002, REQ-BACKUP-003
**Dependencies:** JP-DEC-009
**Blocked by / decisions:** None

Back up database/media/configuration under approved retention.

**Acceptance criteria**

- Database and required files/config are backed up.
- Backup copy exists off application server.
- Access/encryption controls are applied.
- Retention matches approved decision.
- Backup failures are visible.
