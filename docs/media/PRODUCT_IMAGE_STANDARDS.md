# Product Image Standards — JP-CAT-004

## Source assets

- Keep the highest-quality approved supplier/manufacturer source available; do not upscale low-resolution images.
- Prefer a neutral product-first image as the featured image. Additional gallery images may show packaging, texture, usage or scale when truthful.
- Use descriptive filenames before upload, for example `cerave-foaming-cleanser-473ml-front.jpg`.
- Decorative images use empty alt text. Product images require concise alt text that identifies the visible product/packaging without keyword stuffing.

## Generated renditions

Jouvence Para registers:

- `jp-product-card`: maximum 480×480 logical display box;
- `jp-product-gallery`: maximum 1200×1200 logical display box.

WordPress keeps the original source and generates its normal responsive `srcset` variants. JPEG/PNG derivatives are converted to AVIF when the active WordPress image editor reports AVIF support, otherwise WebP when supported. If neither format is available, WordPress keeps its normal source format. Quality for JPEG/WebP/AVIF derivatives is 82.

The theme must request an appropriate registered image size instead of loading full-size originals for cards.

## Storage and CDN boundary

The current implementation intentionally uses WordPress attachment storage and URL APIs. It does not hard-code a filesystem path, object-storage vendor or CDN hostname. This matches ADR-0010: an external media/CDN adapter can rewrite normal WordPress attachment URLs later without changing catalog/domain logic.

Development and staging must use their own uploads/storage. Production media must never be copied into development with customer/runtime data.
