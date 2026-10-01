<?php

declare(strict_types=1);

namespace JouvencePara\Core\Customers;

use JouvencePara\Core\Contracts\Module;
use Throwable;
use WC_Customer;

final class WishlistModule implements Module
{
    private const ENDPOINT = 'wishlist';
    private const META_KEY = '_jp_wishlist_product_ids';
    private const ENDPOINT_VERSION = '1';
    private const FORM_ACTION = 'jp_wishlist_action';
    private const PRODUCT_FIELD = 'jp_wishlist_product_id';
    private const NONCE_FIELD = 'jp_wishlist_nonce';
    private const REQUEST_FIELD = 'jp_wishlist_request_id';
    private const CART_REQUESTS_SESSION_KEY = 'jp_wishlist_cart_requests';
    private const CART_REQUEST_LIMIT = 32;

    public function register(): void
    {
        add_action('init', [$this, 'registerEndpoint'], 99);
        add_filter('woocommerce_account_menu_items', [$this, 'accountMenuItems']);
        add_action('woocommerce_account_' . self::ENDPOINT . '_endpoint', [$this, 'renderWishlist']);
        add_action('template_redirect', [$this, 'handleRequest'], 1);
        add_action('woocommerce_after_add_to_cart_form', [$this, 'renderProductButton']);
    }

    public function registerEndpoint(): void
    {
        add_rewrite_endpoint(self::ENDPOINT, EP_ROOT | EP_PAGES);
        if (get_option('jp_wishlist_endpoint_version', '') === self::ENDPOINT_VERSION) {
            return;
        }
        flush_rewrite_rules(false);
        update_option('jp_wishlist_endpoint_version', self::ENDPOINT_VERSION, false);
    }

    /** @param array<string, string> $items @return array<string, string> */
    public function accountMenuItems(array $items): array
    {
        if (! is_user_logged_in()) {
            return $items;
        }

        $wishlist = [self::ENDPOINT => __('Ma liste de souhaits', 'jouvence-para-core')];
        $position = array_search('orders', array_keys($items), true);
        if ($position === false) {
            $items[self::ENDPOINT] = $wishlist[self::ENDPOINT];
            return $items;
        }

        return array_slice($items, 0, $position + 1, true)
            + $wishlist
            + array_slice($items, $position + 1, null, true);
    }

    public function renderProductButton(): void
    {
        global $product;
        if (! is_object($product) || ! method_exists($product, 'get_id') || ! $this->isPublicProduct($product)) {
            return;
        }

        $productId = (int) $product->get_id();
        if ($productId < 1) {
            return;
        }
        if (! is_user_logged_in()) {
            $accountUrl = function_exists('wc_get_page_permalink') ? (string) wc_get_page_permalink('myaccount') : '';
            if ($accountUrl !== '') {
                echo '<p class="jp-wishlist-login"><a href="' . esc_url($accountUrl) . '">' . esc_html__('Connectez-vous pour enregistrer ce produit dans votre liste.', 'jouvence-para-core') . '</a></p>';
            }
            return;
        }

        $contains = in_array($productId, $this->productIds(), true);
        $action = $contains ? 'remove' : 'add';
        $label = $contains ? __('Retirer de ma liste', 'jouvence-para-core') : __('Ajouter à ma liste de souhaits', 'jouvence-para-core');
        $this->renderForm($productId, $action, 'product', $label, 'jp-wishlist-toggle');
    }

    public function renderWishlist(): void
    {
        if (! is_user_logged_in()) {
            echo '<p>' . esc_html__('Connectez-vous pour consulter votre liste de souhaits.', 'jouvence-para-core') . '</p>';
            return;
        }

        echo '<section class="jp-wishlist" aria-labelledby="jp-wishlist-title">';
        echo '<h2 id="jp-wishlist-title">' . esc_html__('Ma liste de souhaits', 'jouvence-para-core') . '</h2>';
        $productIds = $this->productIds();
        if ($productIds === []) {
            echo '<p>' . esc_html__('Votre liste est vide.', 'jouvence-para-core') . '</p></section>';
            return;
        }

        echo '<ul class="jp-wishlist__items">';
        foreach ($productIds as $productId) {
            $product = function_exists('wc_get_product') ? wc_get_product($productId) : false;
            echo '<li class="jp-wishlist__item">';
            if (! is_object($product) || ! $this->isPublicProduct($product)) {
                echo '<span>' . esc_html__('Produit indisponible', 'jouvence-para-core') . '</span>';
                $this->renderForm($productId, 'remove', 'wishlist', __('Retirer', 'jouvence-para-core'), 'jp-wishlist__remove');
                echo '</li>';
                continue;
            }

            $name = method_exists($product, 'get_name') ? (string) $product->get_name() : '';
            $url = method_exists($product, 'get_permalink') ? (string) $product->get_permalink() : '';
            $available = $this->isAvailable($product);
            echo '<a href="' . esc_url($url) . '">';
            if (method_exists($product, 'get_image')) {
                echo wp_kses_post($product->get_image());
            }
            echo '<span>' . esc_html($name) . '</span></a>';
            if (! $available) {
                echo '<p class="jp-wishlist__availability">' . esc_html__('Actuellement indisponible', 'jouvence-para-core') . '</p>';
            } elseif (method_exists($product, 'is_type') && $product->is_type('simple')) {
                $this->renderForm($productId, 'cart', 'wishlist', __('Ajouter au panier', 'jouvence-para-core'), 'jp-wishlist__cart');
            } else {
                $label = method_exists($product, 'is_type') && ($product->is_type('variable') || $product->is_type('grouped'))
                    ? __('Choisir les options', 'jouvence-para-core')
                    : __('Voir le produit', 'jouvence-para-core');
                echo '<a class="jp-wishlist__options" href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
            }
            $this->renderForm($productId, 'remove', 'wishlist', __('Retirer', 'jouvence-para-core'), 'jp-wishlist__remove');
            echo '</li>';
        }
        echo '</ul></section>';
    }

    public function handleRequest(): void
    {
        if (! isset($_POST[self::FORM_ACTION])) {
            return;
        }

        $userId = get_current_user_id();
        $rawAction = $_POST[self::FORM_ACTION];
        $rawProductId = $_POST[self::PRODUCT_FIELD] ?? null;
        $nonce = $_POST[self::NONCE_FIELD] ?? null;
        $requestId = $_POST[self::REQUEST_FIELD] ?? null;
        if (! is_user_logged_in() || ! current_user_can('read') || $userId < 1
            || ! is_string($rawAction) || ! in_array($rawAction, ['add', 'remove', 'cart'], true)
            || (! is_string($rawProductId) && ! is_int($rawProductId))
            || ! is_string($nonce) || ($rawAction === 'cart' && ! is_string($requestId))) {
            wp_die(esc_html__('Cette action de liste de souhaits n’est pas autorisée.', 'jouvence-para-core'), '', ['response' => 403]);
        }

        $productIdText = is_int($rawProductId) ? (string) $rawProductId : sanitize_text_field(wp_unslash($rawProductId));
        if (! preg_match('/\A[1-9][0-9]*\z/D', $productIdText)) {
            wp_die(esc_html__('Produit invalide.', 'jouvence-para-core'), '', ['response' => 400]);
        }
        $productId = (int) $productIdText;
        $nonce = sanitize_text_field(wp_unslash($nonce));
        if (! wp_verify_nonce($nonce, $this->nonceAction($userId, $productId))) {
            wp_die(esc_html__('Cette action de liste de souhaits a expiré. Rechargez la page et réessayez.', 'jouvence-para-core'), '', ['response' => 403]);
        }
        if ($rawAction === 'cart' && ! preg_match('/\A[a-f0-9-]{36}\z/iD', sanitize_text_field(wp_unslash($requestId)))) {
            wp_die(esc_html__('Cette demande d’ajout au panier est invalide.', 'jouvence-para-core'), '', ['response' => 400]);
        }

        $product = function_exists('wc_get_product') ? wc_get_product($productId) : false;
        $success = $this->processAction($rawAction, $userId, $productId, is_string($requestId) ? sanitize_text_field(wp_unslash($requestId)) : '');
        $messages = [
            'add' => [__('Produit ajouté à votre liste.', 'jouvence-para-core'), __('Ce produit ne peut pas être enregistré dans votre liste.', 'jouvence-para-core')],
            'remove' => [__('Produit retiré de votre liste.', 'jouvence-para-core'), __('La liste n’a pas pu être mise à jour. Réessayez.', 'jouvence-para-core')],
            'cart' => [__('Produit ajouté au panier.', 'jouvence-para-core'), __('Ce produit ne peut pas être ajouté au panier. Vérifiez sa disponibilité et ses options.', 'jouvence-para-core')],
        ];
        wc_add_notice($success ? $messages[$rawAction][0] : $messages[$rawAction][1], $success ? 'success' : 'error');

        $origin = isset($_POST['jp_wishlist_origin']) && is_string($_POST['jp_wishlist_origin'])
            ? sanitize_key(wp_unslash($_POST['jp_wishlist_origin']))
            : '';
        $destination = $origin === 'product' && is_object($product) && $this->isPublicProduct($product)
            && method_exists($product, 'get_permalink')
                ? (string) $product->get_permalink()
                : (function_exists('wc_get_account_endpoint_url') ? (string) wc_get_account_endpoint_url(self::ENDPOINT) : home_url('/'));
        wp_safe_redirect($destination);
        exit;
    }

    public function processAction(string $action, int $userId, int $productId, string $requestId = ''): bool
    {
        if ($userId < 1 || $userId !== get_current_user_id() || $productId < 1) {
            return false;
        }
        if ($action === 'remove') {
            return $this->removeProduct($userId, $productId);
        }
        $product = function_exists('wc_get_product') ? wc_get_product($productId) : false;
        if ($action === 'add') {
            return is_object($product) && $this->isPublicProduct($product) && $this->addProduct($userId, $productId);
        }
        if ($action === 'cart') {
            return preg_match('/\A[a-f0-9-]{36}\z/iD', $requestId) === 1 && $this->addProductToCart($product, $requestId);
        }
        return false;
    }

    /** @return list<int> */
    private function productIds(): array
    {
        $userId = get_current_user_id();
        if ($userId < 1) {
            return [];
        }
        return $this->storedProductIds($userId) ?? [];
    }

    private function addProduct(int $userId, int $productId): bool
    {
        $productIds = $this->storedProductIds($userId);
        if ($productIds === null || (! in_array($productId, $productIds, true) && count($productIds) >= WishlistPolicy::MAX_ITEMS)) {
            return false;
        }
        return $this->updateProductIds($userId, WishlistPolicy::add($productIds, $productId));
    }

    private function removeProduct(int $userId, int $productId): bool
    {
        $productIds = $this->storedProductIds($userId);
        return $productIds !== null && $this->updateProductIds($userId, WishlistPolicy::remove($productIds, $productId));
    }

    /** @return list<int>|null A read failure must not be mistaken for an empty list. */
    private function storedProductIds(int $userId): ?array
    {
        if ($userId < 1 || $userId !== get_current_user_id()) {
            return null;
        }
        try {
            return WishlistPolicy::productIds((new WC_Customer($userId))->get_meta(self::META_KEY));
        } catch (Throwable) {
            return null;
        }
    }

    /** @param list<int> $productIds */
    private function updateProductIds(int $userId, array $productIds): bool
    {
        if ($userId < 1 || $userId !== get_current_user_id()) {
            return false;
        }
        try {
            $customer = new WC_Customer($userId);
            $customer->update_meta_data(self::META_KEY, WishlistPolicy::productIds($productIds));
            $customer->save_meta_data();
            return WishlistPolicy::productIds((new WC_Customer($userId))->get_meta(self::META_KEY)) === WishlistPolicy::productIds($productIds);
        } catch (Throwable) {
            return false;
        }
    }

    private function addProductToCart(mixed $product, string $requestId): bool
    {
        if (! is_object($product) || ! $this->isPublicProduct($product) || ! $this->isAvailable($product)
            || ! method_exists($product, 'is_type') || ! $product->is_type('simple')
            || ! function_exists('WC') || ! is_object(WC()) || ! isset(WC()->cart, WC()->session)
            || ! is_callable([WC()->cart, 'add_to_cart']) || ! is_callable([WC()->session, 'get'])
            || ! is_callable([WC()->session, 'set'])) {
            return false;
        }
        $woocommerce = WC();
        $processedRequests = $woocommerce->session->get(self::CART_REQUESTS_SESSION_KEY, []);
        $processedRequests = is_array($processedRequests) ? $processedRequests : [];
        if (array_key_exists($requestId, $processedRequests)) {
            if ((int) $processedRequests[$requestId] !== (int) $product->get_id()) {
                return false;
            }
            if ($this->cartContainsProduct($woocommerce->cart, (int) $product->get_id())) {
                return true;
            }
            unset($processedRequests[$requestId]);
        }
        $quantity = method_exists($product, 'get_min_purchase_quantity') ? (int) $product->get_min_purchase_quantity() : 1;
        if ($quantity < 1 || ! $woocommerce->cart->add_to_cart((int) $product->get_id(), $quantity)) {
            return false;
        }
        $processedRequests[$requestId] = (int) $product->get_id();
        $processedRequests = array_slice($processedRequests, -self::CART_REQUEST_LIMIT, null, true);
        $woocommerce->session->set(self::CART_REQUESTS_SESSION_KEY, $processedRequests);
        return true;
    }

    private function cartContainsProduct(object $cart, int $productId): bool
    {
        if (! is_callable([$cart, 'get_cart'])) {
            return false;
        }
        foreach ($cart->get_cart() as $item) {
            if (is_array($item) && (int) ($item['product_id'] ?? 0) === $productId) {
                return true;
            }
        }
        return false;
    }

    private function isPublicProduct(object $product): bool
    {
        if (! method_exists($product, 'get_id') || (int) $product->get_id() < 1 || ! method_exists($product, 'is_visible')) {
            return false;
        }
        return $product->is_visible() && get_post_status((int) $product->get_id()) === 'publish';
    }

    private function isAvailable(object $product): bool
    {
        return method_exists($product, 'is_purchasable') && $product->is_purchasable()
            && method_exists($product, 'is_in_stock') && $product->is_in_stock();
    }

    private function renderForm(int $productId, string $action, string $origin, string $label, string $class): void
    {
        $userId = get_current_user_id();
        echo '<form class="' . esc_attr($class) . '" method="post">';
        echo '<input type="hidden" name="' . esc_attr(self::FORM_ACTION) . '" value="' . esc_attr($action) . '">';
        echo '<input type="hidden" name="' . esc_attr(self::PRODUCT_FIELD) . '" value="' . esc_attr((string) $productId) . '">';
        echo '<input type="hidden" name="jp_wishlist_origin" value="' . esc_attr($origin) . '">';
        echo '<input type="hidden" name="' . esc_attr(self::REQUEST_FIELD) . '" value="' . esc_attr(wp_generate_uuid4()) . '">';
        wp_nonce_field($this->nonceAction($userId, $productId), self::NONCE_FIELD);
        echo '<button type="submit">' . esc_html($label) . '</button></form>';
    }

    private function nonceAction(int $userId, int $productId): string
    {
        return 'jp_wishlist_' . $userId . '_' . $productId;
    }
}
