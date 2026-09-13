# Dependency Vulnerability Monitoring — JP-SEC-004

## Automated coverage

The `Security monitoring` GitHub Actions workflow runs on pull requests, pushes to `main`, weekly, and on demand.

It covers:

- Composer metadata and locked Composer packages when a lockfile exists;
- tracked-file secret hygiene;
- filesystem dependency/configuration scanning with Trivy;
- the WordPress and MySQL runtime container images used by local/current infrastructure;
- optional staging WordPress/WooCommerce/plugin/theme vulnerability scanning through WPScan when `JP_SECURITY_SCAN_URL` and `WPSCAN_API_TOKEN` are configured;
- GitHub Actions and Composer update monitoring through Dependabot.

High/critical repository findings and any vulnerability record returned by WPScan fail the workflow. Runtime-image findings are advisory on pull requests and normal pushes because official base images can contain upstream findings that application changes cannot repair; scheduled and manual audits enforce them so they still create an actionable maintenance signal without making every merge appear broken.

## Required staging configuration

Configure the repository variable `JP_SECURITY_SCAN_URL` with the non-production public staging URL and the secret `WPSCAN_API_TOKEN` with an approved WPScan API token. Do not place either value in source files.

The staging scan is intentionally skipped when those values are absent; production credentials or customer data must never be used as a workaround.

Scanner and runtime image references are digest-pinned so a previously reviewed workflow cannot silently execute different container contents. Dependabot does not update Docker references in workflow shell commands, so review and refresh these digests deliberately during the regular dependency-maintenance cycle.

The local database runs the MySQL 8.4 LTS line. Its digest must be updated in both `docker-compose.yml` and the security workflow together after a clean vulnerability scan and compatibility smoke test.

## Update process

1. A dependency alert or failed scheduled scan creates an engineering action.
2. Update the affected dependency/image in a dedicated branch.
3. Run repository checks and the security workflow.
4. Validate the update on staging before production deployment.
5. For urgent exploitable vulnerabilities, disable/remove the affected component when that is safer than waiting for a normal release window.

Runtime WordPress plugins must remain intentionally minimal. Every newly approved plugin must be visible to the staging inventory scan.
