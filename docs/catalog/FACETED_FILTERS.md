# Faceted filters — JP-DISC-002

Approved groups map to controlled taxonomies only. A filter group uses an `IN` clause (OR between values); separate groups are appended to an `AND` tax query. Request values are slug-sanitized, deduplicated and capped at ten values per group.

Facet options are computed from published products in the current product category plus every active group except the group being counted. One bounded product-ID query is reused when equivalent states occur; terms are aggregated in one taxonomy call per group. No per-option N+1 product query is used.
