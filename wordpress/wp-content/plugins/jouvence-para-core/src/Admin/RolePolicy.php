<?php

declare(strict_types=1);

namespace JouvencePara\Core\Admin;

final class RolePolicy
{
    public const STORE_MANAGER = 'jp_store_manager';
    public const CATALOG_MANAGER = 'jp_catalog_manager';
    public const ORDER_OPERATOR = 'jp_order_operator';
    public const SUPPORT_OPERATOR = 'jp_support_operator';

    /** @return array<string, array{name: string, capabilities: array<string, bool>}> */
    public static function definitions(): array
    {
        $productRead = [
            'read' => true,
            'read_product' => true,
            'read_private_products' => true,
        ];
        $catalog = $productRead + [
            'edit_product' => true,
            'edit_products' => true,
            'edit_others_products' => true,
            'edit_published_products' => true,
            'publish_products' => true,
            'delete_product' => true,
            'delete_products' => true,
            'delete_published_products' => true,
            'assign_product_terms' => true,
            'manage_product_terms' => true,
            'edit_product_terms' => true,
            'delete_product_terms' => true,
            'upload_files' => true,
        ];
        $orders = [
            'read' => true,
            'read_shop_order' => true,
            'read_private_shop_orders' => true,
            'edit_shop_order' => true,
            'edit_shop_orders' => true,
            'edit_others_shop_orders' => true,
            'edit_private_shop_orders' => true,
            'edit_published_shop_orders' => true,
        ];

        return [
            self::STORE_MANAGER => [
                'name' => 'Responsable boutique',
                'capabilities' => $catalog + $orders + [
                    'jp_view_audit_log' => true,
                    'jp_manage_merchandising' => true,
                ],
            ],
            self::CATALOG_MANAGER => [
                'name' => 'Responsable catalogue',
                'capabilities' => $catalog,
            ],
            self::ORDER_OPERATOR => [
                'name' => 'Opérateur commandes',
                'capabilities' => $orders,
            ],
            self::SUPPORT_OPERATOR => [
                'name' => 'Support client',
                'capabilities' => [
                    'read' => true,
                    'read_shop_order' => true,
                    'read_private_shop_orders' => true,
                    'edit_shop_order' => true,
                    'edit_shop_orders' => true,
                ],
            ],
        ];
    }

    /** @return list<string> */
    public static function forbiddenAdministrativeCapabilities(): array
    {
        return [
            'manage_options', 'activate_plugins', 'deactivate_plugins', 'install_plugins', 'update_plugins',
            'delete_plugins', 'edit_plugins', 'edit_themes', 'install_themes', 'update_themes', 'delete_themes',
            'create_users', 'delete_users', 'edit_users', 'promote_users', 'remove_users', 'switch_themes',
            'manage_woocommerce', 'jp_process_refunds',
        ];
    }

    public static function isJouvenceStaffRole(string $role): bool
    {
        return isset(self::definitions()[$role]);
    }
}
