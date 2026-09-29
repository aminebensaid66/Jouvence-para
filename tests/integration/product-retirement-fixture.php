<?php

declare(strict_types=1);

namespace {
    final class JP_Test_Retirable_Product extends WC_Product
    {
        public int $metaSaves = 0;
        public function save_meta_data(): void { $this->metaSaves++; }
    }

    function jp_test_retirement_product(bool $confirmed = true): JP_Test_Retirable_Product
    {
        jp_test_seo_page(['permalinks' => [41 => 'https://store.example/produit/a/', 42 => 'https://store.example/produit/b/']]);
        $GLOBALS['jp_test_capability'] = true;
        $GLOBALS['jp_test_nonce_valid'] = true;
        $GLOBALS['jp_test_options'] = [];
        $GLOBALS['jp_retirement_headers'] = [];
        $product = new JP_Test_Retirable_Product(['id' => 41, 'status' => 'publish', 'stock_quantity' => 0]);
        $product->update_meta_data(\JouvencePara\Core\Discovery\ProductRetirementPolicy::CONFIRMED, $confirmed ? 'yes' : 'no');
        $GLOBALS['jp_test_products'] = [41 => $product, 42 => new WC_Product(['id' => 42, 'status' => 'publish'])];
        $_POST = [];
        return $product;
    }
}

namespace JouvencePara\Core\Discovery {
    function wp_verify_nonce(string $nonce, string $action): bool
    {
        return $nonce === 'valid' && $action === 'jp_product_retirement' && $GLOBALS['jp_test_nonce_valid'];
    }
    function remove_action(string $hook, string $callback): void { $GLOBALS['jp_retirement_headers']['removed_action'] = [$hook, $callback]; }
    function status_header(int $status): void { $GLOBALS['jp_retirement_headers']['status'] = $status; }
    function nocache_headers(): void { $GLOBALS['jp_retirement_headers']['no_cache'] = true; }
}
