# JP-SEO-002 — Crawl and product retirement policy

Sources: REQ-SEO-004, REQ-SEO-005; Sprint 5; JP-DISC-003 and JP-SEO-001.

## Crawl policy

| Resource | Index policy | Canonical |
| --- | --- | --- |
| Published product, category, brand, shop | Indexable under the site's global settings | Stable public resource URL |
| Unfiltered archive pagination | Crawlable/indexable | Corresponding page number, never forced to page 1 |
| Custom `jp_filter_*` controlled facets | `noindex,follow`, including empty/malformed values | Archive URL with page number |
| Native WooCommerce `filter_*`, `query_type_*`, price/rating/stock/sale parameters | `noindex,follow` | Archive URL with page number |
| Nondefault/invalid/array sort state | `noindex,follow` | Archive URL with page number |
| Explicit default `orderby=relevance` | Same content policy as base | Clean archive URL |
| Tracking parameters | Same content policy as base | Clean resource URL |

No parameter-based filter landing page is indexable at launch. Curated category,
brand and needs taxonomy pages are the approved landing-page mechanism. Future
indexable filter combinations require an explicit reviewed route/content policy.
Pagination retains validated facet/sort state for navigation. Canonicals strip
request filter/sort/tracking data and use native WordPress pagination settings,
including `paged` with plain permalinks. A global `nofollow` directive remains
in force; this module never weakens it or emits contradictory `index/noindex`.
Do not block these variant URLs in robots.txt: crawlers must fetch them to see
the noindex/canonical directives. No additional sitemap URL variants are created.
If an external SEO plugin owns metadata, configure and verify this same policy
in that plugin before enabling it; the native head ownership handoff alone does
not guarantee that plugin's treatment of custom filter parameters.

## Product removal policy

| Product situation | Response |
| --- | --- |
| Published, temporarily unavailable/out of stock | Keep public page and URL; native WooCommerce availability/Offer data |
| Draft/private, ordinary deletion or unknown URL | Native WordPress 404; no fabricated replacement |
| Explicitly confirmed permanent retirement of a previously published product | Record original URL when native trash/delete runs |
| Recorded retirement with staff-designated, still-published relevant replacement | 301 to that product on the same site |
| Recorded retirement without a valid public replacement | 410, noindex, uncached missing-page template |
| Product restored / original URL reused by a published product | Native public page takes precedence; restoring removes its own record |

Staff must decide relevance. There are no automatic redirects to a category,
homepage, similar product, unpublished product, variation or an external site.
Native WordPress guessed-permalink redirects are disabled for missing product
queries; canonical normalization for existing public pages remains native.
Redirect chains are not inferred: if the selected replacement disappears, the
old URL becomes 410 until staff updates its retirement configuration.

In **Product data → Advanced**, save **Retrait définitif confirmé** and the
optional relevant replacement product ID before removing the published product.
Zero/empty replacement means no replacement. Unauthorized saves, missing/invalid
nonces, self-replacements, nonexistent/nonpublic products and invalid IDs leave
the prior configuration unchanged. Selecting these fields does not remove a
product or modify its stock, price, purchase rules or publication status.

Native trash and permanent-delete hooks persist one non-autoloaded WordPress
option per normalized old route, with original/replacement product IDs only.
Tracking/filter parameters are excluded. Plain product identity query parameters
are retained when needed. The record survives hard deletion; repeated callbacks
upsert it instead of creating duplicate redirect records. No custom table or
schema migration is required. Only confirmed, previously published routes receive
records; the module does not guess historical URLs of products already deleted.

Restoring a product removes only its own record, clears its saved retirement
route and withdraws the permanent confirmation. A new retirement must be
confirmed again. To change a trashed product's replacement, restore it, update
and save the fields, publish if appropriate, then remove it again. For a product
already hard-deleted, an authorized operator can inspect/update the documented
`jp_seo_retired_<sha256(route)>` option via WordPress CLI; validate the replacement
product and test the resulting redirect before publishing the change.

## Verification and rollback

Automated tests cover URL normalization, malformed input, plain permalinks,
same-site redirect validation, repeated deletion, replacement visibility changes,
untrash cleanup, nonce/capability checks, temporary-page preservation and 410
headers. Crawl tests cover invalid/native parameters and contradictory robots.

Focused WordPress/WooCommerce release checks:

1. Inspect robots and canonical tags on unfiltered page 2, filtered page 2,
   native price/attribute parameters and malformed sorting.
2. Publish a test product, set stock to zero and verify the same URL remains 200
   with native out-of-stock availability. No retirement record should be inferred.
3. Save confirmed retirement with no replacement, trash the product and verify
   410/noindex/no-cache, including its original URL with tracking parameters.
4. Repeat with a manually chosen public replacement; verify one 301 to its
   canonical URL. Making that replacement private must switch the old URL to 410.
5. Restore and republish the original: verify the original public page returns
   200 and permanent confirmation is cleared. Verify hard deletion preserves the
   previously captured retirement response, while ordinary deletion stays 404.
6. Repeat on the target permalink configuration and verify no external SEO plugin
   or page cache changes the documented head/HTTP responses.

These runtime release checks are documented; automated fixture tests do not
constitute a deployed WooCommerce/browser test. Roll back by reverting the commit;
retirement options remain inert while the module is disabled, preserving the
operator's explicit decisions. Purge any cached old URLs after restoration or
rollback. Never bulk-delete retirement records without checking their source
and current replacement mapping.
