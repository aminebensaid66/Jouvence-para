<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

use JouvencePara\Core\Contracts\Module;
use WC_Admin_Meta_Boxes;
use WC_Data_Exception;
use WC_Product;

final class CatalogModule implements Module
{
    private WooProductIdentifiers $identifiers;
    private WooProductDataMapper $mapper;
    private ProductDataValidator $validator;

    /** @var array<int, true> */
    private array $adminProcessedObjects = [];

    public function __construct(
        ?WooProductIdentifiers $identifiers = null,
        ?WooProductDataMapper $mapper = null,
        ?ProductDataValidator $validator = null
    ) {
        $this->identifiers = $identifiers ?? new WooProductIdentifiers();
        $this->mapper = $mapper ?? new WooProductDataMapper($this->identifiers);
        $this->validator = $validator ?? new ProductDataValidator($this->identifiers);
    }

    public function register(): void
    {
        add_action('init', [$this, 'registerMeta']);
        add_action('woocommerce_product_options_inventory_product_data', [$this, 'renderEanField']);
        add_action('woocommerce_admin_process_product_object', [$this, 'processAdminProduct'], 100);
        add_action('woocommerce_before_product_object_save', [$this, 'beforeProductSave'], 100, 2);
        add_filter('wc_get_price_decimals', [$this, 'priceDecimals']);
    }

    public function registerMeta(): void
    {
        $args = [
            'type' => 'string',
            'single' => true,
            'show_in_rest' => false,
            'sanitize_callback' => static fn (mixed $value): string => ProductIdentifier::normalizeEan(
                sanitize_text_field((string) $value)
            ),
        ];

        register_post_meta('product', WooProductIdentifiers::LEGACY_EAN_META_KEY, $args);
        register_post_meta('product_variation', WooProductIdentifiers::LEGACY_EAN_META_KEY, $args);
    }

    public function renderEanField(): void
    {
        if (WooProductIdentifiers::nativeGlobalIdAvailable() || ! function_exists('woocommerce_wp_text_input')) {
            return;
        }

        global $post;
        $product = isset($post->ID) && function_exists('wc_get_product') ? wc_get_product((int) $post->ID) : false;
        $value = $product instanceof WC_Product ? $this->identifiers->readEan($product) : '';

        woocommerce_wp_text_input([
            'id' => WooProductIdentifiers::LEGACY_EAN_META_KEY,
            'label' => __('EAN / barcode', 'jouvence-para-core'),
            'value' => $value,
            'desc_tip' => true,
            'description' => __('Unique EAN or barcode supplied by the manufacturer.', 'jouvence-para-core'),
        ]);
    }

    public function processAdminProduct(WC_Product $product): void
    {
        if (! $this->authorizedAdminSave($product)) {
            return;
        }

        if (! WooProductIdentifiers::nativeGlobalIdAvailable()) {
            $rawEan = isset($_POST[WooProductIdentifiers::LEGACY_EAN_META_KEY])
                ? sanitize_text_field(wp_unslash((string) $_POST[WooProductIdentifiers::LEGACY_EAN_META_KEY]))
                : '';
            $this->identifiers->writeEan($product, $rawEan);
        } else {
            $this->migrateLegacyEan($product);
        }

        $this->prepareForValidation($product);
        $result = $this->validator->validate($this->mapper->map($product));
        if ($result->hasHardErrors()) {
            $this->restoreRejectedValues($product, $result);
        }

        foreach ($result->allErrors() as $code) {
            $this->addAdminError($code);
        }
        if ($this->isPublicationStatus((string) $product->get_status('edit')) && ! $result->publicationReady()) {
            $product->set_status('draft');
        }

        $this->adminProcessedObjects[spl_object_id($product)] = true;
    }

    public function beforeProductSave(WC_Product $product, mixed $dataStore = null): void
    {
        unset($dataStore);
        $objectId = spl_object_id($product);
        if (isset($this->adminProcessedObjects[$objectId])) {
            unset($this->adminProcessedObjects[$objectId]);
            return;
        }

        $this->migrateLegacyEan($product);
        $this->prepareForValidation($product);
        $result = $this->validator->validate($this->mapper->map($product));
        if ($result->hasHardErrors()) {
            $messages = array_map([$this, 'messageFor'], $result->hardErrors());
            throw new WC_Data_Exception(
                'jp_invalid_product_data',
                implode(' ', $messages),
                400,
                ['errors' => $result->hardErrors()]
            );
        }
        if ($this->isPublicationStatus((string) $product->get_status('edit')) && ! $result->publicationReady()) {
            $product->set_status('draft');
        }
    }

    public function priceDecimals(int $decimals): int
    {
        if (function_exists('get_woocommerce_currency') && get_woocommerce_currency() === 'TND') {
            return TndMoney::DECIMALS;
        }

        return $decimals;
    }

    private function prepareForValidation(WC_Product $product): void
    {
        $product->set_backorders('no');
        if (function_exists('get_woocommerce_currency') && get_woocommerce_currency() === 'TND') {
            $this->normalizePrice($product, 'regular');
            $this->normalizePrice($product, 'sale');
        }
    }

    private function normalizePrice(WC_Product $product, string $kind): void
    {
        $value = $kind === 'regular'
            ? (string) $product->get_regular_price('edit')
            : (string) $product->get_sale_price('edit');
        if ($value === '') {
            return;
        }

        $normalized = TndMoney::normalize($value);
        if ($normalized === null || str_starts_with($normalized, '-')) {
            return;
        }

        if ($kind === 'regular') {
            $product->set_regular_price($normalized);
        } else {
            $product->set_sale_price($normalized);
        }
    }

    private function authorizedAdminSave(WC_Product $product): bool
    {
        if (! isset($_POST['woocommerce_meta_nonce'], $_POST['post_ID'])) {
            return false;
        }

        $nonce = sanitize_text_field(wp_unslash((string) $_POST['woocommerce_meta_nonce']));
        $postId = absint(wp_unslash($_POST['post_ID']));
        return $postId === (int) $product->get_id()
            && wp_verify_nonce($nonce, 'woocommerce_save_data')
            && current_user_can('edit_post', $postId);
    }

    private function migrateLegacyEan(WC_Product $product): void
    {
        if (! WooProductIdentifiers::nativeGlobalIdAvailable()) {
            return;
        }
        if ((string) $product->get_global_unique_id('edit') !== '') {
            return;
        }

        $legacy = ProductIdentifier::normalizeEan(
            (string) $product->get_meta(WooProductIdentifiers::LEGACY_EAN_META_KEY, true, 'edit')
        );
        if ($legacy !== '') {
            $this->identifiers->writeEan($product, $legacy);
        }
    }

    private function restoreRejectedValues(WC_Product $product, ProductValidationResult $result): void
    {
        $original = null;
        if ($product->get_id() > 0 && function_exists('wc_get_product')) {
            $loaded = wc_get_product($product->get_id());
            $original = $loaded instanceof WC_Product && $loaded !== $product ? $loaded : null;
        }

        if ($result->hasHardError('duplicate_sku')) {
            $product->set_sku($original instanceof WC_Product ? (string) $original->get_sku('edit') : '');
        }
        if ($result->hasHardError('duplicate_ean')) {
            $this->identifiers->writeEan(
                $product,
                $original instanceof WC_Product ? $this->identifiers->readEan($original) : ''
            );
        }
        if ($result->hasHardError('invalid_regular_price') || $result->hasHardError('negative_regular_price')) {
            $product->set_regular_price(
                $original instanceof WC_Product ? (string) $original->get_regular_price('edit') : ''
            );
        }
        if ($result->hasHardError('invalid_sale_price') || $result->hasHardError('negative_sale_price')) {
            $product->set_sale_price($original instanceof WC_Product ? (string) $original->get_sale_price('edit') : '');
        }
        if ($result->hasHardError('negative_stock_quantity')) {
            $product->set_stock_quantity($original instanceof WC_Product ? $original->get_stock_quantity('edit') : null);
        }
        if ($result->hasHardError('invalid_stock_status')) {
            $product->set_stock_status(
                $original instanceof WC_Product ? (string) $original->get_stock_status('edit') : 'outofstock'
            );
        }

        foreach (['weight', 'length', 'width', 'height'] as $field) {
            if (! $result->hasHardError('invalid_' . $field) && ! $result->hasHardError('negative_' . $field)) {
                continue;
            }
            $getter = 'get_' . $field;
            $setter = 'set_' . $field;
            $product->{$setter}($original instanceof WC_Product ? (string) $original->{$getter}('edit') : '');
        }
    }

    private function isPublicationStatus(string $status): bool
    {
        return in_array($status, ['publish', 'future'], true);
    }

    private function addAdminError(string $code): void
    {
        if (class_exists(WC_Admin_Meta_Boxes::class)) {
            WC_Admin_Meta_Boxes::add_error($this->messageFor($code));
        }
    }

    private function messageFor(string $code): string
    {
        return match ($code) {
            'duplicate_sku' => __('The SKU is already used by another product.', 'jouvence-para-core'),
            'duplicate_ean' => __('The EAN / barcode is already used by another product.', 'jouvence-para-core'),
            'invalid_regular_price' => __('The regular price is invalid.', 'jouvence-para-core'),
            'negative_regular_price' => __('The regular price cannot be negative.', 'jouvence-para-core'),
            'invalid_sale_price' => __('The promotional price is invalid.', 'jouvence-para-core'),
            'negative_sale_price' => __('The promotional price cannot be negative.', 'jouvence-para-core'),
            'negative_stock_quantity' => __('Stock quantity cannot be negative.', 'jouvence-para-core'),
            'invalid_stock_status' => __('Stock status must be in stock or out of stock; backorders are disabled.', 'jouvence-para-core'),
            'invalid_weight', 'invalid_length', 'invalid_width', 'invalid_height' => __('Package measurements must be numeric.', 'jouvence-para-core'),
            'negative_weight', 'negative_length', 'negative_width', 'negative_height' => __('Package measurements cannot be negative.', 'jouvence-para-core'),
            'missing_name' => __('A product name is required before publication.', 'jouvence-para-core'),
            'missing_sku' => __('A unique SKU is required before publication.', 'jouvence-para-core'),
            'missing_regular_price' => __('A regular price is required before publication.', 'jouvence-para-core'),
            'stock_management_required' => __('Stock management must be enabled before publication.', 'jouvence-para-core'),
            'missing_stock_quantity' => __('A stock quantity is required before publication.', 'jouvence-para-core'),
            'missing_description' => __('A French short or complete description is required before publication.', 'jouvence-para-core'),
            'missing_brand' => __('A controlled brand is required before publication.', 'jouvence-para-core'),
            'missing_category' => __('At least one product category is required before publication.', 'jouvence-para-core'),
            'missing_featured_image' => __('A featured product image is required before publication.', 'jouvence-para-core'),
            default => __('Product data is not ready for publication.', 'jouvence-para-core'),
        };
    }
}
