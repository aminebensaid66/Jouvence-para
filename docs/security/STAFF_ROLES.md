# Staff roles and least privilege — JP-ADMIN-002

Jouvence Para staff must not use the WordPress Administrator role for routine work.

| Role | Catalog | Orders | Customer support | Refunds | Payment/settings | Plugins/users/security |
|---|---|---|---|---|---|---|
| Responsable boutique | Manage products/taxonomies | Edit orders | View order data | No | No | No |
| Responsable catalogue | Manage products/taxonomies | No | No | No | No | No |
| Opérateur commandes | No | Edit orders | Order data needed for fulfilment | No | No | No |
| Support client | No | Limited order access | Order data needed for support | No | No | No |
| Administrator | Full | Full | Full | Explicitly authorized | Explicitly authorized | Full |

The module strips administrative capabilities from Jouvence Para staff roles even if another component accidentally grants them later. WooCommerce refund AJAX actions are denied for Jouvence Para staff roles unless an explicitly authorized capability is present; no staff role receives that capability in this issue.
