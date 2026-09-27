# Controlled catalog CSV — JP-CAT-003

The CSV contract uses the exact headers documented by `CatalogCsvSchema`. Category and brand columns contain stable slugs. Imports never create taxonomy terms. Unknown controlled values, duplicate SKUs inside a file, invalid prices/stock/status, invalid EAN and persistence failures are reported per row.

Existing products are updated by SKU; new products are created. A missing `status` defaults to `draft`. Export iterates in bounded pages of 100 and emits stable identifiers suitable for controlled re-import.

Files are limited to 5 MB and 5,000 rows per request. Staff need `edit_products`, the administration action uses a WordPress nonce, and uploaded temporary files are read only after WordPress/PHP marks them as uploaded files.
