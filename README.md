# Jouvence Para

Production-oriented WordPress and WooCommerce storefront for the Tunisian parapharmacy market.

## Architecture

- `wordpress/wp-content/themes/jouvence-para`: presentation and accessible frontend behavior.
- `wordpress/wp-content/plugins/jouvence-para-core`: business rules and integration boundaries.
- WooCommerce: products, prices, carts, coupons, customers, orders, and base inventory.
- `docs`: requirements, architecture decisions, planning, operations, and development workflow.

See [the architecture guide](docs/architecture/ARCHITECTURE.md) and [patch workflow](docs/workflow/PATCH_WORKFLOW.md).

## Local development

Requirements: Docker Desktop and Docker Compose.

```bash
cp .env.example .env
docker compose up -d
```

Open <http://localhost:8080>, finish the WordPress installation, install WooCommerce, then activate:

1. **Jouvence Para Core** under Plugins.
2. **Jouvence Para** under Appearance → Themes.

Stop the environment with:

```bash
docker compose down
```

Persistent database and WordPress runtime data live in Docker volumes. Only the custom theme and plugin are versioned.

## Repository workflow

- Branching and releases: `docs/workflow/BRANCHING_AND_RELEASES.md`
- Repository ownership audit: `docs/architecture/REPOSITORY_AUDIT.md`
- One issue per patch/PR unless an approved plan explicitly groups changes.

## Checks

```bash
make check
```

## Documentation

- Production specification: `docs/requirements/JOUVENCE_PARA_PRODUCTION_REQUIREMENTS.md`
- Competitor analysis: `docs/requirements/ECOMMERCE_COMPETITOR_ANALYSIS_AND_PRODUCT_REQUIREMENTS.md`
- Sprint plans: `docs/planning/`
- Architecture decisions: `docs/adr/`

No production secrets or customer/runtime data may be committed.
