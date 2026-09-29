<?php

declare(strict_types=1);

use JouvencePara\Core\Discovery\ProductRetirementModule;
use JouvencePara\Core\Discovery\ProductRetirementPolicy as Policy;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Contracts/Module.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/ProductRetirementPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/ProductRetirementModule.php';
require_once __DIR__ . '/seo-fixture.php';
require_once __DIR__ . '/product-retirement-fixture.php';

return [
    'registers native lifecycle and authorized product-editor hooks' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new ProductRetirementModule())->register();
        $actions = $GLOBALS['jp_test_hooks']['action'];
        foreach (['wp_trash_post', 'before_delete_post', 'untrashed_post', 'woocommerce_product_options_advanced', 'woocommerce_admin_process_product_object'] as $hook) {
            $test->assertTrue(isset($actions[$hook]), $hook);
        }
        $test->assertSame(1, $actions['template_redirect'][0][1]);
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['do_redirect_guess_404_permalink']));
    },
    'requires confirmation and prior publication without changing stock or primary product status' => static function (TestHarness $test): void {
        $module = new ProductRetirementModule();
        $product = jp_test_retirement_product(false);
        $module->recordRemoval(41);
        $test->assertSame(null, $module->response('/produit/a/'));
        $product->update_meta_data(Policy::CONFIRMED, 'yes');
        $product->set_status('draft');
        $module->recordRemoval(41);
        $test->assertSame(null, $module->response('/produit/a/'));
        $product->set_status('publish');
        $module->recordRemoval(41);
        $test->assertSame(0, $product->get_stock_quantity());
        $test->assertSame('publish', $product->get_status());
        $test->assertSame(['status' => 410, 'url' => ''], $module->response('/produit/a/?utm_source=mail'));
    },
    'trash and permanent deletion replay one record and revalidate replacement visibility' => static function (TestHarness $test): void {
        $product = jp_test_retirement_product();
        $product->update_meta_data(Policy::REPLACEMENT, 42);
        $module = new ProductRetirementModule();
        $module->recordRemoval(41);
        $product->set_status('trash');
        $GLOBALS['jp_seo_context']['permalinks'][41] = 'https://store.example/produit/a__trashed/';
        $module->recordRemoval(41);
        $test->assertSame(1, count($GLOBALS['jp_test_options']));
        unset($GLOBALS['jp_test_products'][41]);
        $test->assertSame(['status' => 301, 'url' => 'https://store.example/produit/b/'], $module->response('/produit/a/'));
        $GLOBALS['jp_test_products'][42]->set_status('private');
        $test->assertSame(['status' => 410, 'url' => ''], $module->response('/produit/a/'));
        $test->assertSame(null, $module->response('/unknown/'));
    },
    'restoring a product removes only its own route and withdraws permanent confirmation' => static function (TestHarness $test): void {
        $product = jp_test_retirement_product();
        $module = new ProductRetirementModule();
        $module->recordRemoval(41);
        $module->restore(41);
        $module->restore(41);
        $test->assertSame(null, $module->response('/produit/a/'));
        $test->assertSame('', $product->get_meta(Policy::ROUTE));
        $test->assertSame('no', $product->get_meta(Policy::CONFIRMED));
        $product->update_meta_data(Policy::CONFIRMED, 'yes');
        $module->recordRemoval(41);
        $key = Policy::optionName('/produit/a');
        $GLOBALS['jp_test_options'][$key]['product_id'] = 99;
        $module->restore(41);
        $test->assertSame(99, $GLOBALS['jp_test_options'][$key]['product_id'], 'Do not erase a newer retirement of a reused URL');
    },
    'product-editor writes reject missing nonce permissions and invalid replacement IDs' => static function (TestHarness $test): void {
        $product = jp_test_retirement_product(false);
        $module = new ProductRetirementModule();
        $_POST = [Policy::CONFIRMED => 'yes', Policy::REPLACEMENT => '42'];
        $module->saveFields($product);
        $test->assertSame('no', $product->get_meta(Policy::CONFIRMED));
        $_POST['jp_product_retirement_nonce'] = 'valid';
        $GLOBALS['jp_test_nonce_valid'] = false;
        $module->saveFields($product);
        $test->assertSame('no', $product->get_meta(Policy::CONFIRMED));
        $GLOBALS['jp_test_nonce_valid'] = true;
        $GLOBALS['jp_test_capability'] = false;
        $module->saveFields($product);
        $test->assertSame('no', $product->get_meta(Policy::CONFIRMED));
        $GLOBALS['jp_test_capability'] = true;
        foreach (['41', '99', '-1', ['42']] as $id) {
            $_POST[Policy::REPLACEMENT] = $id;
            $module->saveFields($product);
            $test->assertSame('no', $product->get_meta(Policy::CONFIRMED));
        }
        $_POST[Policy::REPLACEMENT] = '42';
        $GLOBALS['jp_test_products'][42]->set_status('private');
        $module->saveFields($product);
        $test->assertSame('no', $product->get_meta(Policy::CONFIRMED));
        $GLOBALS['jp_test_products'][42]->set_status('publish');
        $module->saveFields($product);
        $test->assertSame('yes', $product->get_meta(Policy::CONFIRMED));
        $test->assertSame(42, $product->get_meta(Policy::REPLACEMENT));
        $_POST = [];
    },
    'only known missing routes emit uncached noindex 410 and disable guessed redirects' => static function (TestHarness $test): void {
        jp_test_retirement_product();
        $module = new ProductRetirementModule();
        $module->recordRemoval(41);
        $_SERVER['REQUEST_URI'] = '/produit/a/';
        $module->handleMissingProduct();
        $test->assertSame([], $GLOBALS['jp_retirement_headers'], 'Published or temporarily unavailable page stays intact');
        $GLOBALS['jp_seo_context']['404'] = true;
        $module->handleMissingProduct();
        $test->assertSame(410, $GLOBALS['jp_retirement_headers']['status']);
        $test->assertSame(true, $GLOBALS['jp_retirement_headers']['no_cache']);
        $test->assertSame(['template_redirect', 'redirect_canonical'], $GLOBALS['jp_retirement_headers']['removed_action']);
        unset($_SERVER['REQUEST_URI']);
    },
    'missing product slugs cannot redirect to guessed unrelated resources' => static function (TestHarness $test): void {
        $module = new ProductRetirementModule();
        jp_test_seo_page(['product' => 'unknown-product']);
        $test->assertSame(false, $module->allowGuessedRedirect(true));
        jp_test_seo_page(['post_type' => 'product']);
        $test->assertSame(false, $module->allowGuessedRedirect(true));
        jp_test_seo_page(['post_type' => ['product']]);
        $test->assertSame(false, $module->allowGuessedRedirect(true));
        jp_test_seo_page(['post_type' => 'post']);
        $test->assertSame(true, $module->allowGuessedRedirect(true));
        $test->assertSame(false, $module->allowGuessedRedirect(false));
    },
];
