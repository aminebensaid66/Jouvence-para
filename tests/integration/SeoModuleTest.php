<?php

declare(strict_types=1);

use JouvencePara\Core\Discovery\SeoModule;
use JouvencePara\Core\Discovery\DiscoveryRoutingModule;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Contracts/Module.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/SeoPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/SeoModule.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/SortPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/FilterState.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/DiscoveryUrlPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/DiscoveryRoutingModule.php';
require_once __DIR__ . '/seo-fixture.php';

return [
    'registers the metadata schema URL and rewrite contracts once in core' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new SeoModule())->register();
        $filters = $GLOBALS['jp_test_hooks']['filter'] ?? [];
        $actions = $GLOBALS['jp_test_hooks']['action'] ?? [];
        $test->assertTrue(isset($filters['register_post_type_args']));
        $test->assertTrue(isset($filters['register_taxonomy_args']));
        $test->assertTrue(isset($filters['post_link']));
        $test->assertTrue(isset($actions['init']));
        $test->assertTrue(isset($actions['wp_head']));
        $registeredHead = array_column($actions['wp_head'], 1);
        $test->assertSame([3, 6, 7, 9], $registeredHead);

        $GLOBALS['jp_test_hooks'] = [];
        (new DiscoveryRoutingModule())->register();
        $discoveryHead = array_column($GLOBALS['jp_test_hooks']['action']['wp_head'] ?? [], 1);
        $test->assertSame([], $discoveryHead);
    },
    'renders escaped product metadata and social image without a second product graph or canonical' => static function (TestHarness $test): void {
        jp_test_seo_page(['post' => new WP_Post(['post_type' => 'product']), 'title' => 'Soin "été" & beauté']);
        $GLOBALS['jp_test_products'][41] = new WC_Product(['short_description' => '<p>Soin &amp; beauté</p>', 'image_id' => 300]);
        $module = new SeoModule();
        $head = jp_test_seo_output(static function () use ($module): void {
            $module->metadata();
            $module->archiveCanonical();
            $module->articleSchema();
        });

        $test->assertTrue(str_contains($head, 'content="Soin &amp; beauté"'));
        $test->assertTrue(str_contains($head, 'Soin &quot;été&quot; &amp; beauté'));
        $test->assertTrue(str_contains($head, 'content="https://store.example/image-300.jpg"'));
        $test->assertTrue(! str_contains($head, 'rel="canonical"'), 'WordPress owns singular canonicals');
        $test->assertTrue(! str_contains($head, 'application/ld+json'), 'WooCommerce owns product graphs');
    },
    'excludes private transactional search preview and unavailable pages from metadata' => static function (TestHarness $test): void {
        foreach (['cart', 'checkout', 'account', 'search', 'feed', 'preview', '404'] as $flag) {
            jp_test_seo_page(['post' => new WP_Post(['post_type' => 'page']), $flag => true]);
            $module = new SeoModule();
            $test->assertSame('', jp_test_seo_output([$module, 'metadata']), $flag);
            $test->assertSame('', jp_test_seo_output([$module, 'archiveCanonical']), $flag);
        }
        foreach ([['post_status' => 'draft'], ['post_password' => 'private']] as $properties) {
            jp_test_seo_page(['post' => new WP_Post($properties)]);
            $module = new SeoModule();
            $test->assertSame('', jp_test_seo_output([$module, 'metadata']));
            $test->assertSame('', jp_test_seo_output([$module, 'articleSchema']));
        }
    },
    'archive canonicals omit request filters and use the native pagination base' => static function (TestHarness $test): void {
        jp_test_seo_page(['tax' => true, 'paged' => 2, 'term' => (object) ['description' => '<p>Soins visage</p>']]);
        $GLOBALS['wp_rewrite']->pagination_base = 'pages';
        $_GET = ['orderby' => 'price', 'jp_filter_brand' => ['marque']];
        try {
            $module = new SeoModule();
            $head = jp_test_seo_output([$module, 'archiveCanonical']);
            $test->assertSame('<link rel="canonical" href="https://store.example/categorie/visage/pages/2/">' . "\n", $head);
            $test->assertTrue(str_contains(jp_test_seo_output([$module, 'metadata']), 'content="Soins visage"'));
            $GLOBALS['jp_test_options']['permalink_structure'] = '';
            $GLOBALS['jp_seo_context']['term_url'] = 'https://store.example/?product_cat=visage';
            $test->assertTrue(str_contains(jp_test_seo_output([$module, 'archiveCanonical']), '?product_cat=visage&amp;paged=2'));
        } finally {
            $_GET = [];
        }
    },
    'empty shop content falls back to its page title instead of duplicate site descriptions' => static function (TestHarness $test): void {
        jp_test_seo_page(['shop' => true, 'title' => 'Boutique – Jouvence Para']);
        $head = jp_test_seo_output([new SeoModule(), 'metadata']);
        $test->assertTrue(str_contains($head, 'name="description" content="Boutique – Jouvence Para"'));
        $test->assertTrue(str_contains($head, 'content="https://store.example/boutique/"'));
    },
    'article and organization JSON only use published visible headline and site values' => static function (TestHarness $test): void {
        jp_test_seo_page(['post' => new WP_Post(['post_title' => 'Soin &amp; beauté <em>été</em>'])]);
        $module = new SeoModule();
        $head = jp_test_seo_output(static function () use ($module): void {
            $module->organizationSchema();
            $module->articleSchema();
        });
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $head, $matches);
        $nodes = array_map(static fn (string $json): array => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $matches[1]);
        $test->assertSame('Organization', $nodes[0]['@type']);
        $test->assertSame('Jouvence & Para', $nodes[0]['name']);
        $test->assertSame('Article', $nodes[1]['@type']);
        $test->assertSame('Soin & beauté été', $nodes[1]['headline']);
        $test->assertSame($nodes[0]['@id'], $nodes[1]['publisher']['@id']);
        $test->assertTrue(! isset($nodes[1]['aggregateRating'], $nodes[1]['author']));
    },
    'SEO plugin ownership suppresses duplicate metadata canonicals and schemas' => static function (TestHarness $test): void {
        foreach (['WPSEO_VERSION', 'RANK_MATH_VERSION', 'SEOPRESS_VERSION'] as $constant) {
            jp_test_seo_page(['tax' => true, 'external_constants' => [$constant]]);
            $module = new SeoModule();
            $head = jp_test_seo_output(static function () use ($module): void {
                $module->metadata();
                $module->archiveCanonical();
                $module->organizationSchema();
                $module->articleSchema();
            });
            $test->assertSame('', $head, $constant);
        }
        jp_test_seo_page();
    },
    'advice rewrites flush softly once and leave plain permalinks unchanged' => static function (TestHarness $test): void {
        jp_test_seo_page();
        unset($GLOBALS['jp_test_options']['jp_seo_permalink_version']);
        $GLOBALS['jp_seo_flushes'] = [];
        $module = new SeoModule();
        $module->registerArticleRewrite();
        $module->registerArticleRewrite();
        $test->assertSame([false], $GLOBALS['jp_seo_flushes']);
        $post = new WP_Post(['post_name' => 'routine']);
        $test->assertSame('https://store.example/conseils/routine/', $module->articlePermalink('old', $post, false));
        $GLOBALS['jp_test_options']['permalink_structure'] = '';
        $test->assertSame('https://store.example/?p=41', $module->articlePermalink('https://store.example/?p=41', $post, false));
    },
];
