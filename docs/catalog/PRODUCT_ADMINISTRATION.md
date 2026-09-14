# Product administration — JP-CAT-005

WooCommerce's native product editor remains the administration surface for names, SKU/EAN, descriptions, pricing and sale dates, stock, images, variations, categories and controlled attributes. Jouvence Para Core adds a compact publication-readiness panel driven by the canonical product validator rather than duplicating those fields.

Internal supplier/source references are stored as private product metadata, excluded from REST exposure and editable only by users who can edit the product and products generally. Each save validates a Jouvence Para nonce in addition to the surrounding WooCommerce product-save authorization.

Brand/category/attribute terms must already exist. Product editors assign controlled terms; they do not create arbitrary terms through these internal fields.
