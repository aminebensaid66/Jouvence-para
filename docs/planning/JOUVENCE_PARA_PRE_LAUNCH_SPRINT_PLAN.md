# Jouvence Para — Pre-launch Plan

## Sprint goal

> Validate the real production system and data under release conditions, prove recovery, run pilot orders, and make the formal GO/NO-GO decision.

## Entry criteria

- All applicable P0 implementation work from Sprints 1–6 is complete on staging.
- Production catalog and real operational contacts are available.
- Monitoring, backups and deployment flow are active.

## Assigned tickets

| Ticket ID     | Title                                                          | Type   | Priority   | Workstream                            | Dependencies                                      | Blocked By / Decisions   |
|:--------------|:---------------------------------------------------------------|:-------|:-----------|:--------------------------------------|:--------------------------------------------------|:-------------------------|
| JP-PERF-002   | Run backend/load performance testing against approved capacity | Story  | P0         | Backend / DevOps / QA                 | JP-DEC-009, JP-PERF-001                           |                          |
| JP-BACKUP-002 | Test restoration and RPO/RTO runbook                           | Story  | P0         | DevOps / QA                           | JP-BACKUP-001, JP-DEC-009                         |                          |
| JP-QA-002     | Run supported browser/device release matrix                    | Story  | P0         | QA / Frontend                         | JP-QA-001                                         |                          |
| JP-DATA-001   | Remove demo data and migrate validated production catalog      | Story  | P0         | Catalog / Backend / QA                | JP-CAT-003, JP-DEC-002                            |                          |
| JP-LAUNCH-001 | Execute production GO/NO-GO checklist and pilot orders         | Story  | P0         | Tech Lead / Product / QA / Operations | JP-QA-002, JP-DATA-001, JP-BACKUP-002, JP-OBS-002 |                          |

## Continuous engineering control carried through this sprint

| Ticket ID   | Title                               | Type   | Priority   | Workstream                    | Dependencies   | Blocked By / Decisions   |
|:------------|:------------------------------------|:-------|:-----------|:------------------------------|:---------------|:-------------------------|
| JP-SEC-003  | Apply custom-code security controls | Story  | P0         | Backend / Frontend / Security | JP-PLAT-002    |                          |

## Suggested parallel lanes

### Performance / DevOps
- **JP-PERF-002** — Run backend/load performance testing against approved capacity
- **JP-BACKUP-002** — Test restoration and RPO/RTO runbook

### QA
- **JP-QA-002** — Run supported browser/device release matrix

### Catalog / Data
- **JP-DATA-001** — Remove demo data and migrate validated production catalog

### Product / Tech Lead / Operations
- **JP-LAUNCH-001** — Execute production GO/NO-GO checklist and pilot orders

## Exit criteria

- Capacity/load targets are tested.
- Backup restore is proven against RPO/RTO.
- Supported browser/device matrix passes.
- Validated production catalog replaces demo data.
- Real pilot orders complete through operational processing.
- Formal GO/NO-GO decision is recorded.
- Custom-code security controls remain enforced for all changes in the sprint.

## Dependency / blocker handling

A ticket must not be implemented by inventing an unresolved business rule. If its `Blocked By / Decisions` field is unresolved, the team may implement only the non-dependent foundation and must keep the behavior disabled or incomplete until the decision is approved.

## Ticket details

### JP-PERF-002 — Run backend/load performance testing against approved capacity
**Type:** Story
**Priority:** P0
**Workstream:** Backend / DevOps / QA
**Requirements:** Architecture / operational requirement
**Dependencies:** JP-DEC-009, JP-PERF-001
**Blocked by / decisions:** None

Validate search, cart and checkout under realistic catalog/traffic assumptions.

**Acceptance criteria**

- Load-test scenario matches approved expected capacity.
- Search/add-to-cart/checkout latency is measured at p95.
- Bottlenecks and DB/cache behavior are recorded.
- Critical capacity failures are resolved or accepted before GO.

### JP-BACKUP-002 — Test restoration and RPO/RTO runbook
**Type:** Story
**Priority:** P0
**Workstream:** DevOps / QA
**Requirements:** REQ-BACKUP-004, REQ-BACKUP-005
**Dependencies:** JP-BACKUP-001, JP-DEC-009
**Blocked by / decisions:** None

Prove backups can restore a usable system within approved recovery objectives.

**Acceptance criteria**

- Restore test is performed in isolated environment.
- Database/media consistency is checked.
- Measured restore point/time is compared to RPO/RTO.
- Runbook records exact recovery steps and ownership.

### JP-QA-002 — Run supported browser/device release matrix
**Type:** Story
**Priority:** P0
**Workstream:** QA / Frontend
**Requirements:** Architecture / operational requirement
**Dependencies:** JP-QA-001
**Blocked by / decisions:** None

Validate critical journeys on required mobile and desktop browsers.

**Acceptance criteria**

- iPhone/Safari tested.
- Android/Chrome tested.
- Desktop Chrome/Firefox/Edge tested.
- Desktop Safari tested where available.
- Blocking issues are resolved before GO.

### JP-DATA-001 — Remove demo data and migrate validated production catalog
**Type:** Story
**Priority:** P0
**Workstream:** Catalog / Backend / QA
**Requirements:** Architecture / operational requirement
**Dependencies:** JP-CAT-003, JP-DEC-002
**Blocked by / decisions:** None

Perform controlled production-data migration with quality gates.

**Acceptance criteria**

- Demo products/orders/customers/contact data are removed.
- Real taxonomy/brands/products are imported.
- SKU/EAN/price/stock/images/content/SEO checks pass.
- Import QA report is signed off.
- No placeholder legal/contact/social data remains.

### JP-LAUNCH-001 — Execute production GO/NO-GO checklist and pilot orders
**Type:** Story
**Priority:** P0
**Workstream:** Tech Lead / Product / QA / Operations
**Requirements:** Architecture / operational requirement
**Dependencies:** JP-QA-002, JP-DATA-001, JP-BACKUP-002, JP-OBS-002
**Blocked by / decisions:** None

Use the production acceptance criteria as the release gate and complete real pilot orders operationally.

**Acceptance criteria**

- All applicable P0 requirements pass.
- Money/stock/shipping/payment TBDs are resolved.
- Real business/legal content is live.
- Pilot orders complete from checkout through operational processing.
- Monitoring/backups/restore are verified.
- Staff can process products, stock, orders and complaints.
- GO/NO-GO decision is recorded.
