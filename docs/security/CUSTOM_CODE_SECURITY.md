# Custom-code security controls — JP-SEC-003

Custom-code security is a continuous pull-request requirement. Every application-code change follows `AGENTS.md` and the patch review checklist in `docs/workflow/PATCH_WORKFLOW.md`.

## Automated controls

- `make check` runs PHP syntax/style checks, JSON validation, tracked-file secret checks, and unit/integration suites. Security-sensitive handlers must have tests for their permission, nonce/CSRF, input validation and failure paths.
- The `CodeQL` workflow analyzes JavaScript/TypeScript and GitHub Actions workflow code on pull requests, main pushes, merge-queue groups, weekly schedule and manual dispatch with the `security-extended` query suite. Results are uploaded to GitHub code scanning. CodeQL does not support PHP; PHP therefore remains covered by syntax/style checks, security-sensitive unit/integration tests and the required manual code review.
- The `Security monitoring` workflow checks tracked secrets and filesystem findings, audits locked Composer dependencies when present, scans pinned WordPress/MySQL runtime images, and scans the configured staging inventory with WPScan.
- These scanners complement code review and tests. CodeQL findings are visible in code scanning; whether they block merges depends on the repository's required checks and code-scanning protection settings.

## Application-code review requirements

- Validate and sanitize request, REST, settings, metadata and import values before use. Escape output for its HTML, attribute, URL, JavaScript or SQL context.
- Every state-changing admin, AJAX, REST or customer action must authorize the current principal and verify its nonce, CSRF token or provider signature before writing. Define safe retry behavior for repeated requests.
- Use WordPress/WooCommerce CRUD and query APIs. Any direct SQL must use `$wpdb->prepare()` and bounded inputs.
- Accept uploads only through the WordPress upload API, enforce an allowlist and size limit on the server, and prevent executable files from being served. Existing catalog CSV import is limited by type, size, rows, editor capability, nonce and `is_uploaded_file()`.
- Use the shared redacting logger for application events. Never log passwords, session material, access tokens, raw payment data or unnecessary personal data.
- Keep provider secrets in environment-managed configuration, outside Git and browser code. Development/staging integrations must fail closed rather than use production credentials.

## Runtime boundary

This ticket covers application-code controls. TLS, HSTS, secure cookies, production headers and host-specific secret injection must be configured through the selected production platform and are tracked by the deployment/security issues. A CodeQL upload is a report, not a substitute for enforcing required checks and alert handling in GitHub repository settings.
