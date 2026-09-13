# Observability Baseline — JP-OBS-001

The core plugin provides a provider-independent baseline so production diagnostics do not depend on a monitoring vendor.

## Health endpoint

`GET /wp-json/jouvence-para/v1/health` returns only service status, release and time. It intentionally exposes no database, customer or infrastructure detail and sends `Cache-Control: no-store`. An external uptime monitor can use this endpoint later without code changes.

## Request metrics

The plugin records estimated daily request count, 5xx count, cumulative latency and maximum latency in non-autoloaded WordPress options. Successful requests are sampled at 10% and weighted; every 5xx response is recorded. A short option lock prevents concurrent requests from overwriting one another, and lock contention drops a sample instead of delaying the storefront. Tools → Jouvence Para diagnostics shows:

- requests and 5xx rate;
- average and maximum request time;
- database connectivity;
- object-cache state;
- PHP peak memory;
- disk free/total.

The Site Health test becomes critical when at least 20 requests have been recorded and the daily 5xx rate reaches 5%.

This lightweight in-application baseline is not a substitute for external uptime/APM monitoring at scale. `JP-DEC-009` may later select an external provider; the public health route and structured logs are intended integration points.

## Errors and privacy

Fatal PHP errors are emitted as structured JSON through the normal PHP error logger. Context is bounded and redacts secret-bearing keys, bearer tokens and email addresses. Raw request bodies, passwords, cookies, payment data and tokens are never intentionally logged.

Operational integrations should use the same redaction rule before adding context.
