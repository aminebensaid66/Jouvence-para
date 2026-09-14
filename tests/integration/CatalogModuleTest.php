<?php

declare(strict_types=1);

use JouvencePara\Core\Catalog\CatalogModule;
use JouvencePara\Core\Catalog\WooProductDataMapper;
use JouvencePara\Core\Catalog\WooProductIdentifiers;

if (! class_exists('WC_Data_Exception')) {
    class WC_Data_Exception extends Exception
    {
        /** @param array<string, mixed> $data */
        public function __construct(
            public string $errorCode,
            string $message,
            public int $httpStatusCode = 400,
            public array $data = []
        ) {
            parent::__construct($message);
        }
    }
}

if (! class_exists('WC_Admin_Meta_Boxes')) {
    class WC_Admin_Meta_Boxes
    {
        /** @var list<string> */
        public static array $errors = [];

        public static function add_error(string $message): void
        {
            self::$errors[] = $message;
        }
    }
}

if (! class_exists('WC_Product')) {
    class WC_Product
    {
        /** @var array<string, mixed> */
        private array $data;
        /** @var array<string, mixed> */
        private array $meta = [];

        /** @param array<string, mixed> $data */
        public function __construct(array $data = [])
        {
            $this->data = array_replace([
                'id' => 41,
                'name' => 'Nettoyant doux 200 ml',
                'sku' => 'JP-000041',
                'short_description' => '<p>Nettoie la peau en douceur.</p>',
                'description' => '',
                'regular_price' => '32.900',
                'sale_price' => '',
                'stock_quantity' => 12,
                'stock_status' => 'instock',
                'manage_stock' => true,
                'backorders' => 'notify',
                'status' => 'draft',
                'image_id' => 300,
                'gallery_image_ids' => [301],
                'weight' => '0.250',
                'length' => '12',
                'width' => '5',
                'height' => '18',
                'category_ids' => [8],
                'type' => 'simple',
            ], $data);
        }

        public function get_id(): int { return (int) $this->data['id']; }
        public function get_name(string $context = 'view'): string { return (string) $this->data['name']; }
        public function get_sku(string $context = 'view'): string { return (string) $this->data['sku']; }
        public function set_sku(string $value): void { $this->data['sku'] = $value; }
        public function get_short_description(string $context = 'view'): string { return (string) $this->data['short_description']; }
        public function get_description(string $context = 'view'): string { return (string) $this->data['description']; }
        public function get_regular_price(string $context = 'view'): string { return (string) $this->data['regular_price']; }
        public function set_regular_price(string $value): void { $this->data['regular_price'] = $value; }
        public function get_sale_price(string $context = 'view'): string { return (string) $this->data['sale_price']; }
        public function set_sale_price(string $value): void { $this->data['sale_price'] = $value; }
        public function get_stock_quantity(string $context = 'view'): int|float|null { return $this->data['stock_quantity']; }
        public function set_stock_quantity(int|float|null $value): void { $this->data['stock_quantity'] = $value; }
        public function get_stock_status(string $context = 'view'): string { return (string) $this->data['stock_status']; }
        public function set_stock_status(string $value): void { $this->data['stock_status'] = $value; }
        public function get_manage_stock(string $context = 'view'): bool { return (bool) $this->data['manage_stock']; }
        public function get_backorders(string $context = 'view'): string { return (string) $this->data['backorders']; }
        public function set_backorders(string $value): void { $this->data['backorders'] = $value; }
        public function get_status(string $context = 'view'): string { return (string) $this->data['status']; }
        public function set_status(string $value): void { $this->data['status'] = $value; }
        public function get_image_id(string $context = 'view'): int { return (int) $this->data['image_id']; }
        /** @return list<int> */
        public function get_gallery_image_ids(string $context = 'view'): array { return $this->data['gallery_image_ids']; }
        public function get_weight(string $context = 'view'): string { return (string) $this->data['weight']; }
        public function set_weight(string $value): void { $this->data['weight'] = $value; }
        public function get_length(string $context = 'view'): string { return (string) $this->data['length']; }
        public function set_length(string $value): void { $this->data['length'] = $value; }
        public function get_width(string $context = 'view'): string { return (string) $this->data['width']; }
        public function set_width(string $value): void { $this->data['width'] = $value; }
        public function get_height(string $context = 'view'): string { return (string) $this->data['height']; }
        public function set_height(string $value): void { $this->data['height'] = $value; }
        /** @return list<int> */
        public function get_category_ids(string $context = 'view'): array { return $this->data['category_ids']; }
        public function is_type(string $type): bool { return $this->data['type'] === $type; }
        public function get_meta(string $key, bool $single = true, string $context = 'view'): mixed { return $this->meta[$key] ?? ''; }
        public function update_meta_data(string $key, mixed $value): void { $this->meta[$key] = $value; }
        public function delete_meta_data(string $key): void { unset($this->meta[$key]); }
    }
}

$GLOBALS['jp_test_hooks'] = [];
$GLOBALS['jp_test_currency'] = 'TND';
$GLOBALS['jp_test_sku_owners'] = [];
$GLOBALS['jp_test_ean_owners'] = [];
$GLOBALS['jp_test_brand_products'] = [41 => true];
$GLOBALS['jp_test_products'] = [];
$GLOBALS['jp_test_nonce_valid'] = true;
$GLOBALS['jp_test_capability'] = true;
$GLOBALS['jp_test_registered_meta'] = [];

if (! function_exists('add_action')) {
    function add_action(string $hook, mixed $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        $GLOBALS['jp_test_hooks']['action'][$hook][] = [$callback, $priority, $acceptedArgs];
        return true;
    }
}
if (! function_exists('add_filter')) {
    function add_filter(string $hook, mixed $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        $GLOBALS['jp_test_hooks']['filter'][$hook][] = [$callback, $priority, $acceptedArgs];
        return true;
    }
}
if (! function_exists('register_post_meta')) {
    function register_post_meta(string $postType, string $key, array $args): bool
    {
        $GLOBALS['jp_test_registered_meta'][$postType][$key] = $args;
        return true;
    }
}
if (! function_exists('sanitize_text_field')) {
    function sanitize_text_field(string $value): string { return trim(strip_tags($value)); }
}
if (! function_exists('wp_unslash')) {
    function wp_unslash(mixed $value): mixed { return $value; }
}
if (! function_exists('absint')) {
    function absint(mixed $value): int { return abs((int) $value); }
}
if (! function_exists('wp_verify_nonce')) {
    function wp_verify_nonce(string $nonce, string $action): int|false
    {
        return $GLOBALS['jp_test_nonce_valid'] && $nonce === 'valid' && $action === 'woocommerce_save_data' ? 1 : false;
    }
}
if (! function_exists('current_user_can')) {
    function current_user_can(string $capability, mixed ...$args): bool
    {
        return (bool) $GLOBALS['jp_test_capability'];
    }
}
if (! function_exists('__')) {
    function __(string $text, string $domain = 'default'): string { return $text; }
}
if (! function_exists('get_woocommerce_currency')) {
    function get_woocommerce_currency(): string { return (string) $GLOBALS['jp_test_currency']; }
}
if (! function_exists('wc_get_product_id_by_sku')) {
    function wc_get_product_id_by_sku(string $sku): int { return (int) ($GLOBALS['jp_test_sku_owners'][$sku] ?? 0); }
}
if (! function_exists('get_posts')) {
    function get_posts(array $args): array
    {
        $ean = (string) ($args['meta_value'] ?? '');
        $owner = (int) ($GLOBALS['jp_test_ean_owners'][$ean] ?? 0);
        $excluded = array_map('intval', (array) ($args['post__not_in'] ?? []));
        return $owner > 0 && ! in_array($owner, $excluded, true) ? [$owner] : [];
    }
}
if (! function_exists('taxonomy_exists')) {
    function taxonomy_exists(string $taxonomy): bool { return in_array($taxonomy, ['jp_brand', 'jp_need', 'product_cat'], true); }
}
if (! function_exists('has_term')) {
    function has_term(string $term, string $taxonomy, int $productId): bool
    {
        return $taxonomy === 'jp_brand' && (bool) ($GLOBALS['jp_test_brand_products'][$productId] ?? false);
    }
}
if (! function_exists('wc_get_product')) {
    function wc_get_product(int $productId): WC_Product|false
    {
        return $GLOBALS['jp_test_products'][$productId] ?? false;
    }
}
if (! function_exists('woocommerce_wp_text_input')) {
    function woocommerce_wp_text_input(array $args): void { $GLOBALS['jp_test_field'] = $args; }
}

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Contracts/Module.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/IdentifierLookup.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/ProductIdentifier.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/TndMoney.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/ProductData.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/ProductValidationResult.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/ProductDataValidator.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/WooProductIdentifiers.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/WooProductDataMapper.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/CatalogModule.php';

$reset = static function (): void {
    $GLOBALS['jp_test_hooks'] = [];
    $GLOBALS['jp_test_currency'] = 'TND';
    $GLOBALS['jp_test_sku_owners'] = [];
    $GLOBALS['jp_test_ean_owners'] = [];
    $GLOBALS['jp_test_brand_products'] = [41 => true];
    $GLOBALS['jp_test_products'] = [];
    $GLOBALS['jp_test_nonce_valid'] = true;
    $GLOBALS['jp_test_capability'] = true;
    $GLOBALS['jp_test_registered_meta'] = [];
    WC_Admin_Meta_Boxes::$errors = [];
    $_POST = [];
};

return [
    'registers catalog validation hooks without touching the theme' => static function (TestHarness $test) use ($reset): void {
        $reset();
        (new CatalogModule())->register();

        foreach (
            [
                'init',
                'woocommerce_product_options_inventory_product_data',
                'woocommerce_admin_process_product_object',
                'woocommerce_before_product_object_save',
            ] as $hook
        ) {
            $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action'][$hook]), 'Missing action ' . $hook);
        }
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['wc_get_price_decimals']));
    },
    'registers the WooCommerce 9.0 EAN compatibility metadata' => static function (TestHarness $test) use ($reset): void {
        $reset();
        $module = new CatalogModule();
        $module->registerMeta();
        $test->assertTrue(isset($GLOBALS['jp_test_registered_meta']['product'][WooProductIdentifiers::LEGACY_EAN_META_KEY]));
        $test->assertTrue(isset($GLOBALS['jp_test_registered_meta']['product_variation'][WooProductIdentifiers::LEGACY_EAN_META_KEY]));
    },
    'uses three decimals only when the active currency is TND' => static function (TestHarness $test) use ($reset): void {
        $reset();
        $module = new CatalogModule();
        $test->assertSame(3, $module->priceDecimals(2));
        $GLOBALS['jp_test_currency'] = 'EUR';
        $test->assertSame(2, $module->priceDecimals(2));
    },
    'maps canonical WooCommerce product fields without a parallel entity' => static function (TestHarness $test) use ($reset): void {
        $reset();
        $product = new WC_Product([
            'status' => 'publish',
            'gallery_image_ids' => [301, 302],
            'weight' => '0.250',
            'length' => '12',
            'width' => '5',
            'height' => '18',
        ]);
        $product->update_meta_data(WooProductIdentifiers::LEGACY_EAN_META_KEY, '6191234567890');

        $data = (new WooProductDataMapper(new WooProductIdentifiers()))->map($product);

        $test->assertSame(41, $data->id);
        $test->assertSame('JP-000041', $data->sku);
        $test->assertSame('6191234567890', $data->ean);
        $test->assertSame([301, 302], $data->galleryImageIds);
        $test->assertSame('0.250', $data->weight);
        $test->assertSame('12', $data->length);
        $test->assertSame('5', $data->width);
        $test->assertSame('18', $data->height);
        $test->assertSame('publish', $data->status);
        $test->assertTrue($data->hasBrand);
    },
    'normalizes TND prices and disables backorders before programmatic save' => static function (TestHarness $test) use ($reset): void {
        $reset();
        $product = new WC_Product(['status' => 'publish', 'regular_price' => '12.3456', 'backorders' => 'notify']);
        (new CatalogModule())->beforeProductSave($product);
        $test->assertSame('12.346', $product->get_regular_price('edit'));
        $test->assertSame('no', $product->get_backorders('edit'));
        $test->assertSame('publish', $product->get_status('edit'));
    },
    'throws a WooCommerce data exception for hard-invalid programmatic data' => static function (TestHarness $test) use ($reset): void {
        $reset();
        $product = new WC_Product(['regular_price' => '-1']);
        try {
            (new CatalogModule())->beforeProductSave($product);
            $test->assertTrue(false, 'Expected WC_Data_Exception');
        } catch (WC_Data_Exception $exception) {
            $test->assertSame('jp_invalid_product_data', $exception->errorCode);
            $test->assertTrue(in_array('negative_regular_price', $exception->data['errors'], true));
        }
    },
    'prevents incomplete products from being published or scheduled' => static function (TestHarness $test) use ($reset): void {
        $reset();
        $product = new WC_Product(['status' => 'future', 'image_id' => 0]);
        (new CatalogModule())->beforeProductSave($product);
        $test->assertSame('draft', $product->get_status('edit'));
    },
    'rejects a programmatic save that reuses another product SKU' => static function (TestHarness $test) use ($reset): void {
        $reset();
        $GLOBALS['jp_test_sku_owners']['JP-000041'] = 77;
        try {
            (new CatalogModule())->beforeProductSave(new WC_Product());
            $test->assertTrue(false, 'Expected duplicate SKU exception');
        } catch (WC_Data_Exception $exception) {
            $test->assertTrue(in_array('duplicate_sku', $exception->data['errors'], true));
        }
    },
    'rejects duplicate compatibility EAN values with a bounded lookup' => static function (TestHarness $test) use ($reset): void {
        $reset();
        $GLOBALS['jp_test_ean_owners']['6191234567890'] = 77;
        $product = new WC_Product();
        $product->update_meta_data(WooProductIdentifiers::LEGACY_EAN_META_KEY, '6191234567890');
        try {
            (new CatalogModule())->beforeProductSave($product);
            $test->assertTrue(false, 'Expected duplicate EAN exception');
        } catch (WC_Data_Exception $exception) {
            $test->assertTrue(in_array('duplicate_ean', $exception->data['errors'], true));
        }
    },
    'requires the WooCommerce nonce and edit capability for admin compatibility fields' => static function (TestHarness $test) use ($reset): void {
        $reset();
        $product = new WC_Product(['backorders' => 'notify']);
        $_POST = [
            'post_ID' => '41',
            'woocommerce_meta_nonce' => 'invalid',
            WooProductIdentifiers::LEGACY_EAN_META_KEY => '619 123 456 7890',
        ];
        (new CatalogModule())->processAdminProduct($product);
        $test->assertSame('notify', $product->get_backorders('edit'));
        $test->assertSame('', $product->get_meta(WooProductIdentifiers::LEGACY_EAN_META_KEY, true, 'edit'));

        $reset();
        $GLOBALS['jp_test_capability'] = false;
        $product = new WC_Product(['backorders' => 'notify']);
        $_POST = [
            'post_ID' => '41',
            'woocommerce_meta_nonce' => 'valid',
            WooProductIdentifiers::LEGACY_EAN_META_KEY => '619 123 456 7890',
        ];
        (new CatalogModule())->processAdminProduct($product);
        $test->assertSame('notify', $product->get_backorders('edit'));
        $test->assertSame('', $product->get_meta(WooProductIdentifiers::LEGACY_EAN_META_KEY, true, 'edit'));
    },
    'restores a previously valid value when admin submits a negative price' => static function (TestHarness $test) use ($reset): void {
        $reset();
        $original = new WC_Product(['regular_price' => '25.000']);
        $GLOBALS['jp_test_products'][41] = $original;
        $product = new WC_Product(['regular_price' => '-2']);
        $_POST = [
            'post_ID' => '41',
            'woocommerce_meta_nonce' => 'valid',
        ];
        (new CatalogModule())->processAdminProduct($product);
        $test->assertSame('25.000', $product->get_regular_price('edit'));
        $test->assertTrue(WC_Admin_Meta_Boxes::$errors !== []);
    },
    'sanitizes and stores EAN during an authorized WooCommerce 9.0 admin save' => static function (TestHarness $test) use ($reset): void {
        $reset();
        $product = new WC_Product();
        $_POST = [
            'post_ID' => '41',
            'woocommerce_meta_nonce' => 'valid',
            WooProductIdentifiers::LEGACY_EAN_META_KEY => ' 619 123 456 7890 ',
        ];
        (new CatalogModule())->processAdminProduct($product);
        $test->assertSame('6191234567890', $product->get_meta(WooProductIdentifiers::LEGACY_EAN_META_KEY, true, 'edit'));
        $test->assertSame('no', $product->get_backorders('edit'));
    },
];
