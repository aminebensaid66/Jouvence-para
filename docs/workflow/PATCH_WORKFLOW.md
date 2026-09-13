# Patch-First Development Workflow

## 1. Why this workflow

Every AI change is delivered as a reviewable unified diff. The developer validates it locally, runs tests, reviews the result, and only then commits and pushes it. ChatGPT never needs GitHub credentials and never pushes directly.

## 2. ChatGPT project setup

Create one ChatGPT Project for Jouvence Para and add these shared sources:

- `AGENTS.md`;
- `docs/requirements/JOUVENCE_PARA_PRODUCTION_REQUIREMENTS.md`;
- `docs/planning/JOUVENCE_PARA_COMPLETE_SPRINT_ROADMAP.md`;
- the current sprint plan under `docs/planning/`;
- `docs/architecture/ARCHITECTURE.md`;
- this workflow.

Use a separate chat for each ticket. Project instructions and sources provide shared context, while separate chats keep each deliverable focused. A ChatGPT Project on the web does not automatically see the local repository; provide the relevant current files for every ticket or use a local project/code workflow that has folder access.

## 3. Before requesting a patch

Start from a clean, current branch:

```bash
git switch main
git pull --ff-only
git switch -c feat/JP-CAT-001-product-model
git status --short
git rev-parse HEAD
```

Prepare a context bundle containing:

- ticket text and acceptance criteria;
- base commit SHA;
- relevant requirement IDs;
- relevant ADRs/business rules;
- repository tree for the affected area;
- complete current contents of every file likely to change;
- versions and available test commands;
- explicit non-goals.

Do not ask ChatGPT to reconstruct current files from an old conversation.

## 4. Ticket prompt template

Copy this into a fresh ticket chat:

```text
Implement ticket <TICKET-ID>: <TITLE>.

Base commit: <FULL-SHA>
Architecture: follow AGENTS.md and docs/architecture/ARCHITECTURE.md.
Requirements: <REQ-IDS>.
Acceptance criteria:
<PASTE CRITERIA>

Relevant current files are attached/pasted in full.
Non-goals:
<LIST>

First analyze dependencies, security, edge cases, and test coverage. If a required business decision or file is missing, stop and list exactly what is missing; do not invent it.

When ready, produce one downloadable plain-text file named:
<TICKET-ID>-<SHORT-DESCRIPTION>.patch

The file must be a git-compatible unified diff relative to repository root, contain no Markdown fences or prose, and include all required tests. Do not include unrelated changes, generated files, secrets, uploads, database dumps, or binary content.

Also provide separately:
- concise summary;
- files changed;
- tests expected;
- assumptions;
- proposed commit message.
```

## 5. Save and validate the patch

Save the downloaded file under `patches/incoming/`, then run:

```bash
git status --short
git apply --check patches/incoming/JP-CAT-001-product-model.patch
git apply --stat patches/incoming/JP-CAT-001-product-model.patch
git apply patches/incoming/JP-CAT-001-product-model.patch
git diff --check
git diff --stat
git diff
```

Never skip `git apply --check`. Never use `--reject`, `--3way`, or manual conflict resolution without reviewing why the patch no longer matches the supplied base.

If validation fails:

1. do not partially apply it;
2. capture the exact error;
3. confirm the current commit and file contents;
4. ask for a regenerated patch against the new snapshot.

## 6. Test and review

Run the ticket-specific checks plus the full fast suite defined by the repository. Review especially:

- unexpected files;
- authentication/authorization;
- sanitization, escaping and nonces;
- WooCommerce API usage and HPOS compatibility;
- stock/order/payment side effects;
- migrations and rollback behavior;
- accessibility;
- logs and personal data;
- query/performance regressions.

For high-risk commerce changes, request a second review in a new chat using the applied `git diff`, acceptance criteria, and test results. The reviewer should not be the same chat that generated the patch.

## 7. Commit and push

Only after local verification:

```bash
git add <EXPLICIT-FILES>
git diff --cached --check
git diff --cached
git commit -m "feat(catalog): implement JP-CAT-001 product model"
git push -u origin feat/JP-CAT-001-product-model
```

Open a pull request containing:

- ticket and requirement IDs;
- behavior summary;
- test evidence;
- screenshots for visual work;
- migration/rollback notes;
- risks and follow-ups.

Do not commit files in `patches/incoming/`; the Git commit is the durable record of the applied change.

## 8. Patch sizing rules

A patch should normally:

- implement one ticket;
- stay under roughly 400 changed lines when practical;
- avoid more than one migration or external integration;
- keep refactors separate from behavior changes;
- remain reviewable in one sitting.

Split a ticket before coding when it combines multiple domain modules, requires several sequential migrations, or cannot be tested independently.

## 9. Team parallelism

- One active branch and owner per ticket.
- Avoid assigning two tickets that modify the same module simultaneously.
- Merge contracts/foundations before dependent UI work.
- Rebase/update the context bundle after every dependency merges.
- Never generate multiple dependent patches against the same old base and apply them later in arbitrary order.
- Integrate small patches frequently instead of holding a sprint-sized mega-patch.
