# Coding Standards

## General

- UTF-8, LF endings, final newline, no accidental trailing whitespace.
- Keep one issue per patch and avoid unrelated formatting churn.
- New identifiers and APIs use English; customer-facing content may be localized.
- Never commit secrets, runtime data, generated dependencies or uploads.

## PHP

- PHP 8.1+ language level unless the platform baseline is raised by ADR.
- WordPress/WooCommerce APIs are preferred over direct database access.
- New object-oriented core-plugin code uses the `JouvencePara\\Core` namespace.
- Validate/sanitize input, authorize state-changing actions, verify nonces/CSRF protection, and escape output by context.
- New PHP files use `declare(strict_types=1);` when compatible with WordPress entry-point conventions.
- PHP syntax is a mandatory local and CI check.

The repository intentionally does not add a PHP formatter that would rewrite existing code wholesale during Sprint 1. A future formatting-tool adoption must be isolated in its own ticket.

## JavaScript and CSS

There is no versioned JavaScript source in the current repository snapshot. When JS source is introduced, its owning ticket must add an executable lint/format command before merging. Frontend code must remain mobile-first, progressively enhanced and WCAG-oriented.

CSS must use the existing theme design system rather than introducing a second styling framework.

## Commands

```bash
make lint-php
make check-style
make check-json
make check
```

`make check` is the mandatory fast repository gate. Docker configuration remains a separate check when Docker is available.
