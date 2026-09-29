# JP-SEO-001 — URL, metadata and structured data

## URL policy

At the French-only launch, stable public paths are:

- product: `/produit/{product-slug}/`;
- product category: `/categorie/{term-slug}/`;
- brand: `/marque/{brand-slug}/`;
- article: `/conseils/{post-slug}/`.

WordPress pages and shop location continue to use their configured slugs.
The core plugin changes only the product and product category rewrite bases,
and preserves WooCommerce's other rewrite settings. Article links use the advice
base while pretty permalinks are enabled. Rules are flushed softly once when
this URL version first runs; no `.htaccess` or server rewrite file is changed.
The brand taxonomy already uses `/marque/`.

There is one canonical French URL per resource and no `hreflang`. Sorted and
filtered product archives retain the existing noindex policy and receive the
base archive canonical; page 2+ canonicals retain their page number using the
native rewrite pagination base or the `paged` parameter with plain permalinks. WordPress
supplies canonical tags on singular pages. The core module supplies shop,
brand, category, blog index and home archive canonical tags. Product archive
filter and sort parameters do not enter those canonical URLs.

This URL change is pre-launch. Before importing production catalog data or
publishing these paths, confirm no public catalog URLs need 301 redirects. If
legacy URLs exist, prepare and test explicit redirects from the actual prior
bases before enabling the new paths.

## Titles and metadata

The theme's WordPress `title-tag` support and `wp_get_document_title()` provide
the page title from the current post or term and the configured site title. The
core module supplies an escaped meta description from the product short
description, page/post excerpt or content, product term description, shop page
content or site description. A manual excerpt or product short description
provides page-specific copy; automatic descriptions are clipped at a word
boundary to 160 Unicode characters. Pages without a source description fall
back to their current document title. Search, 404, feed, preview, cart, checkout
and account pages receive no public-page metadata from this module.

Open Graph title, description, canonical URL and site name are emitted when
description and canonical sources exist. Product social previews reuse the
WooCommerce product image used by the visible product page. Other page images
are omitted unless product media is visible. Twitter summary metadata uses the
same visible values. No alternate locale is fabricated.

A standard Yoast SEO, Rank Math, AIOSEO or SEOPress install owns metadata and
schema output instead; this module detects those plugins and suppresses its own
output to avoid duplicate heads/graphs. No SEO plugin is bundled or required.

## Structured data ownership

- Organization: core emits the configured WordPress site name and home URL.
- Product, Offer and conditional AggregateRating: WooCommerce remains the only
  owner, sourcing values from the live `WC_Product`. This module emits no
  product nodes or ratings.
- BreadcrumbList: WooCommerce generates breadcrumb data when its visible
  breadcrumb is rendered. This module does not emit duplicate or hidden crumbs.
- Article: core emits an Article node only for a published, non-password-
  protected WordPress post. It contains the visible headline, canonical URL and
  site publisher; it does not expose author data or hidden dates/images.

JSON-LD uses WordPress JSON encoding with HTML-significant characters escaped.
No schema value is derived from request parameters or arbitrary provider data.

## Focused release verification

On the target WordPress and WooCommerce versions, with pretty permalinks enabled:

1. Verify product, category, brand and advice single/archive links resolve at
   the paths above.
2. Confirm existing public URLs and the production catalog import plan. Add and
   test redirects before launch if prior URLs exist.
3. Inspect page source for one page title, one description and one canonical on
   product, category, brand, shop, article, home and page 2+.
4. Verify filters and sorting receive `noindex,follow`, a clean archive
   canonical, and never create extra crawlable URL variants.
5. Parse JSON-LD on product pages and verify one Product/Offer graph and no
   AggregateRating until real enabled reviews exist; verify one visible matching
   BreadcrumbList, Organization data and Article on published advice posts.
6. Repeat with an SEO plugin installed; verify one metadata/schema owner remains.

Automated tests cover URL policy, Unicode descriptions, hook registration,
rendered metadata escaping, private-page exclusions, schema JSON, SEO-plugin
ownership, pagination and one-time rewrite flushing. The deployed WordPress/WooCommerce render check is
required before the public catalog is enabled.
