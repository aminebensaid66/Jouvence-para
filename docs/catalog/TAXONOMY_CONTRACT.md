# Controlled catalog taxonomy contract — JP-CAT-002

WooCommerce `product_cat` remains hierarchical and canonical for commercial categories. Jouvence Para Core owns `jp_brand` and `jp_need`, while global WooCommerce attributes provide controlled filter dimensions.

## Controlled vocabularies

| Business dimension | Stable identifier | Management |
|---|---|---|
| Category | `product_cat` | Hierarchical WordPress/WooCommerce taxonomy UI |
| Brand | `jp_brand` | Core-registered taxonomy UI; duplicate slugs rejected |
| Product concern | `jp_need` | Core-registered taxonomy UI; duplicate slugs rejected |
| Skin type | `pa_skin_type` | Global WooCommerce select attribute |
| Hair type | `pa_hair_type` | Global WooCommerce select attribute |
| SPF | `pa_spf` | Global WooCommerce select attribute |
| Size/volume | `pa_size_volume` | Global WooCommerce select attribute |
| Target audience | `pa_target_audience` | Global WooCommerce select attribute |

The attribute definitions are created once by an authorized administrator. Terms are never silently created by this module. Imports must resolve existing stable values or report an error.
