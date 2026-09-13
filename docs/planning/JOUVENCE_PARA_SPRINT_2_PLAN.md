# Jouvence Para — Sprint 2 Plan

## Sprint goal

> Stabilize the catalog, product-content, email and security foundations so product discovery can be built on real, controlled data.

## Entry criteria

- Sprint 1 repository/plugin/test/CI foundations are usable.
- Critical catalog ownership and inventory decisions are sufficiently resolved.
- Representative real products are available for development/testing.

## Assigned tickets

| Ticket ID     | Title                                                          | Type   | Priority   | Workstream                   | Dependencies              | Blocked By / Decisions   |
|:--------------|:---------------------------------------------------------------|:-------|:-----------|:-----------------------------|:--------------------------|:-------------------------|
| JP-CAT-003    | Build controlled CSV import/export workflow                    | Story  | P0         | Backend / Catalog            | JP-CAT-001, JP-CAT-002    |                          |
| JP-CAT-004    | Implement media pipeline and product image standards           | Story  | P0         | Frontend / Backend           | JP-DEC-009, JP-CAT-001    |                          |
| JP-CAT-005    | Implement product administration and validation                | Story  | P0         | Backend / Catalog            | JP-CAT-001, JP-CAT-002    |                          |
| JP-SEARCH-002 | Implement search indexing, normalization and ranking           | Story  | P0         | Backend                      | JP-SEARCH-001, JP-CAT-002 |                          |
| JP-DISC-001   | Build responsive category/product grid                         | Story  | P0         | Frontend                     | JP-CAT-002, JP-CAT-004    |                          |
| JP-PDP-004    | Enforce product content and medical-claim rules                | Story  | P0         | Catalog / Backend            | JP-DEC-002                |                          |
| JP-STOCK-001  | Configure inventory source, overselling and low-stock behavior | Story  | P0         | Backend / Operations         | JP-CAT-001, JP-DEC-003    |                          |
| JP-I18N-001   | Implement French baseline and Arabic-ready architecture        | Story  | P0         | Frontend / Backend / Content | JP-DEC-007, JP-CAT-002    |                          |
| JP-ADMIN-002  | Implement least-privilege staff roles                          | Story  | P0         | Backend / Security           | JP-SEC-002                |                          |
| JP-EMAIL-001  | Configure reliable transactional email delivery and DNS        | Story  | P0         | DevOps / Backend             | JP-DEC-001                |                          |
| JP-SEC-002    | Implement admin 2FA, least privilege and WordPress hardening   | Story  | P0         | Backend / DevOps / Security  | JP-PLAT-001               |                          |
| JP-SEC-004    | Implement dependency vulnerability monitoring                  | Story  | P0         | DevOps / Security            | JP-PLAT-004               |                          |
| JP-OBS-001    | Implement application error tracking, logs and core metrics    | Story  | P0         | DevOps / Backend             | JP-DEC-009                |                          |

## Continuous engineering control carried through this sprint

| Ticket ID   | Title                               | Type   | Priority   | Workstream                    | Dependencies   | Blocked By / Decisions   |
|:------------|:------------------------------------|:-------|:-----------|:------------------------------|:---------------|:-------------------------|
| JP-SEC-003  | Apply custom-code security controls | Story  | P0         | Backend / Frontend / Security | JP-PLAT-002    |                          |

## Suggested parallel lanes

### Backend / Catalog
- **JP-CAT-003** — Build controlled CSV import/export workflow
- **JP-CAT-004** — Implement media pipeline and product image standards
- **JP-CAT-005** — Implement product administration and validation
- **JP-PDP-004** — Enforce product content and medical-claim rules
- **JP-STOCK-001** — Configure inventory source, overselling and low-stock behavior
- **JP-I18N-001** — Implement French baseline and Arabic-ready architecture

### Frontend
- **JP-CAT-004** — Implement media pipeline and product image standards
- **JP-DISC-001** — Build responsive category/product grid
- **JP-I18N-001** — Implement French baseline and Arabic-ready architecture

### DevOps / Security
- **JP-EMAIL-001** — Configure reliable transactional email delivery and DNS
- **JP-SEC-002** — Implement admin 2FA, least privilege and WordPress hardening
- **JP-SEC-004** — Implement dependency vulnerability monitoring
- **JP-OBS-001** — Implement application error tracking, logs and core metrics

### Admin / Operations
- **JP-STOCK-001** — Configure inventory source, overselling and low-stock behavior
- **JP-ADMIN-002** — Implement least-privilege staff roles

## Exit criteria

- Canonical taxonomy and product administration are usable by catalog staff.
- CSV/product-content/media foundations are moving against controlled real data.
- Base inventory behavior is configured.
- French + Arabic-ready architecture is established.
- Transactional email infrastructure is configured.
- Admin hardening, role model and vulnerability monitoring are active.
- Custom-code security controls remain enforced for all changes in the sprint.

## Dependency / blocker handling

A ticket must not be implemented by inventing an unresolved business rule. If its `Blocked By / Decisions` field is unresolved, the team may implement only the non-dependent foundation and must keep the behavior disabled or incomplete until the decision is approved.

## Ticket details

### JP-CAT-003 — Build controlled CSV import/export workflow
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Catalog
**Requirements:** REQ-ADMIN-002
**Dependencies:** JP-CAT-001, JP-CAT-002
**Blocked by / decisions:** None

Provide deterministic catalog ingestion with row-level validation and error reporting.

**Acceptance criteria**

- Documented CSV headers exist.
- Unknown controlled values are reported, not silently duplicated.
- Duplicate SKU rows are rejected/flagged.
- Invalid prices/stock are rejected/flagged.
- Import produces a row-level result report.
- Export preserves stable identifiers needed for re-import.

**Tests**

- Valid import.
- Unknown brand.
- Duplicate SKU.
- Invalid sale price.
- Invalid stock.
- Re-import/update existing SKU.

### JP-CAT-004 — Implement media pipeline and product image standards
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend
**Requirements:** REQ-MEDIA-001, REQ-MEDIA-002, REQ-MEDIA-003
**Dependencies:** JP-DEC-009, JP-CAT-001
**Blocked by / decisions:** None

Standardize product media storage, naming, responsive renditions and delivery.

**Acceptance criteria**

- Image dimension/quality standards are documented.
- Responsive renditions are generated/served.
- WebP/AVIF is used where supported by stack.
- Meaningful alt text can be managed.
- Media storage/CDN behavior matches ADR and does not break staging/production separation.

**Tests**

- Responsive srcset test.
- Missing image fallback.
- Alt text rendering.
- Large source image optimization.

### JP-CAT-005 — Implement product administration and validation
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Catalog
**Requirements:** REQ-ADMIN-001
**Dependencies:** JP-CAT-001, JP-CAT-002
**Blocked by / decisions:** None

Make product data manageable by authorized catalog staff without code changes.

**Acceptance criteria**

- Authorized users can manage product/variation fields and taxonomies.
- Invalid controlled values cannot be introduced casually.
- Price, sale dates and stock fields are usable by catalog staff.
- Internal traceability fields are restricted to authorized staff.

### JP-SEARCH-002 — Implement search indexing, normalization and ranking
**Type:** Story
**Priority:** P0
**Workstream:** Backend
**Requirements:** REQ-SEARCH-001, REQ-SEARCH-002, REQ-SEARCH-004
**Dependencies:** JP-SEARCH-001, JP-CAT-002
**Blocked by / decisions:** None

Index required catalog fields and implement relevance behavior.

**Acceptance criteria**

- Name, brand, SKU, EAN, category, need and active ingredient are searchable.
- Search is case/accent tolerant and handles common spacing/hyphen variants.
- Simple typo tolerance works within chosen engine capability.
- Exact name/SKU/EAN matches outrank weak descriptive matches.
- Ranking weights are configurable/documented.

**Tests**

- Exact SKU/EAN search.
- Accent/no-accent search.
- Misspelling search.
- Brand+name ranking.
- Need/ingredient search.

### JP-DISC-001 — Build responsive category/product grid
**Type:** Story
**Priority:** P0
**Workstream:** Frontend
**Requirements:** REQ-FILTER-001, REQ-CARD-001, REQ-CARD-002
**Dependencies:** JP-CAT-002, JP-CAT-004
**Blocked by / decisions:** None

Build reusable catalog cards and responsive listing layout.

**Acceptance criteria**

- Cards show required product fields and correct current price/stock.
- Out-of-stock state is visually distinct and not normally purchasable.
- Grid works on supported mobile and desktop breakpoints.
- Quick add respects product/variation constraints.

### JP-PDP-004 — Enforce product content and medical-claim rules
**Type:** Story
**Priority:** P0
**Workstream:** Catalog / Backend
**Requirements:** REQ-PRODUCT-006, REQ-CONTENT-001, REQ-CONTENT-002, REQ-CONTENT-003, REQ-CONTENT-004
**Dependencies:** JP-DEC-002
**Blocked by / decisions:** None

Create operational content rules that prevent unapproved medical claims and establish content ownership.

**Acceptance criteria**

- Content owner/reviewer is recorded.
- Catalog workflow identifies source/manufacturer basis.
- No automated text is presented as diagnosis.
- Unsupported healing/guarantee claims are prohibited by editorial workflow.
- Content QA checklist exists.

### JP-STOCK-001 — Configure inventory source, overselling and low-stock behavior
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Operations
**Requirements:** REQ-STOCK-001, REQ-STOCK-002, REQ-STOCK-003
**Dependencies:** JP-CAT-001, JP-DEC-003
**Blocked by / decisions:** None

Establish base WooCommerce inventory behavior and operational low-stock visibility.

**Acceptance criteria**

- Configured source of truth matches inventory ADR.
- Overselling is disabled by default.
- Backorders require explicit rule/product enablement.
- Global and per-product low-stock thresholds work.
- Authorized staff receive/see actionable low-stock alerts.

### JP-I18N-001 — Implement French baseline and Arabic-ready architecture
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend / Content
**Requirements:** REQ-I18N-001, REQ-I18N-002
**Dependencies:** JP-DEC-007, JP-CAT-002
**Blocked by / decisions:** None

Ensure templates, taxonomies, custom fields and SEO can become bilingual without core redesign.

**Acceptance criteria**

- French launch content path works.
- Chosen multilingual solution is documented in ADR.
- Custom fields/taxonomies are translatable where needed.
- Theme has no structural blocker to RTL.
- hreflang/translated slug strategy is documented.

### JP-ADMIN-002 — Implement least-privilege staff roles
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Security
**Requirements:** Architecture / operational requirement
**Dependencies:** JP-SEC-002
**Blocked by / decisions:** None

Create logical admin roles for catalog, orders, support and marketing.

**Acceptance criteria**

- Ordinary staff do not need Administrator.
- Catalog staff cannot manage plugins/users/security.
- Order/support access to customer data is limited to operational need.
- Refund/payment configuration permissions are restricted.
- Permission matrix is documented and tested.

### JP-EMAIL-001 — Configure reliable transactional email delivery and DNS
**Type:** Story
**Priority:** P0
**Workstream:** DevOps / Backend
**Requirements:** REQ-EMAIL-001, REQ-EMAIL-002
**Dependencies:** JP-DEC-001
**Blocked by / decisions:** None

Use managed SMTP/transactional email and configure production deliverability.

**Acceptance criteria**

- Staging and production email credentials are separate.
- SPF/DKIM are configured for production domain.
- DMARC policy is set appropriately.
- Delivery failures are observable.

### JP-SEC-002 — Implement admin 2FA, least privilege and WordPress hardening
**Type:** Story
**Priority:** P0
**Workstream:** Backend / DevOps / Security
**Requirements:** REQ-SEC-002, REQ-SEC-003
**Dependencies:** JP-PLAT-001
**Blocked by / decisions:** None

Harden privileged access and minimize WordPress attack surface.

**Acceptance criteria**

- Administrators use 2FA.
- Brute-force protection is active.
- Production file editor is disabled.
- Unused XML-RPC/interfaces are restricted where not required.
- Privileged user creation is monitored.
- Plugin count/privileges are reviewed.

### JP-SEC-004 — Implement dependency vulnerability monitoring
**Type:** Story
**Priority:** P0
**Workstream:** DevOps / Security
**Requirements:** REQ-SEC-007
**Dependencies:** JP-PLAT-004
**Blocked by / decisions:** None

Continuously identify known vulnerabilities across WordPress/plugins/themes/runtime/frontend dependencies.

**Acceptance criteria**

- Automated or scheduled vulnerability checks exist.
- Critical findings create an operational action.
- Update process uses staging/testing before production where practical.

### JP-OBS-001 — Implement application error tracking, logs and core metrics
**Type:** Story
**Priority:** P0
**Workstream:** DevOps / Backend
**Requirements:** Architecture / operational requirement
**Dependencies:** JP-DEC-009
**Blocked by / decisions:** None

Provide production visibility before feature complexity increases.

**Acceptance criteria**

- Application exceptions include release/environment/route context.
- Sensitive data is redacted.
- Core system metrics and 5xx/error rate are available.
- Checkout/integration failures can be investigated.
