<?php

declare(strict_types=1);

namespace {
    class WC_Customer
    {
        private array $meta;
        public function __construct(private int $id) { $this->meta = $GLOBALS['jp_account_meta'][$id] ?? []; }
        public function get_meta(string $key): mixed
        {
            if ($GLOBALS['jp_account_read_fail'] ?? false) { throw new RuntimeException('Customer meta read failed'); }
            return $this->meta[$key] ?? '';
        }
        public function update_meta_data(string $key, mixed $value): void { $this->meta[$key] = $value; }
        public function save_meta_data(): void
        {
            ++$GLOBALS['jp_account_saves'];
            if (! $GLOBALS['jp_account_save_fail']) { $GLOBALS['jp_account_meta'][$this->id] = $this->meta; }
        }
    }
    if (! class_exists('WP_Error')) {
        class WP_Error
        {
            public array $errors = [];
            public function __construct(public string $code = '', public string $message = '') {}
            public function add(string $code, string $message): void { $this->errors[$code] = $message; }
        }
    }
    function wc_create_page(string $slug, string $option, string $title, string $content): int
    {
        $GLOBALS['jp_account_created'][] = [$slug, $option, $title, $content];
        $GLOBALS['jp_account_page'] = 42;
        return 42;
    }
    function jp_test_account_reset(): void
    {
        $GLOBALS['jp_test_options'] = [];
        $GLOBALS['jp_test_failed_update'] = null;
        $GLOBALS['jp_test_capability'] = true;
        $GLOBALS['jp_account_page'] = 42;
        $GLOBALS['jp_account_page_status'] = 'publish';
        $GLOBALS['jp_account_page_type'] = 'page';
        $GLOBALS['jp_account_page_content'] = '[woocommerce_my_account]';
        $GLOBALS['jp_account_actor'] = 7;
        $GLOBALS['jp_account_meta'] = [];
        $GLOBALS['jp_account_saves'] = 0;
        $GLOBALS['jp_account_save_fail'] = false;
        $GLOBALS['jp_account_read_fail'] = false;
        $GLOBALS['jp_account_nonce_valid'] = true;
        $GLOBALS['jp_account_native_nonce_valid'] = true;
        $GLOBALS['jp_account_notices'] = [];
        $GLOBALS['jp_account_fields'] = [];
        $GLOBALS['jp_account_created'] = [];
        $GLOBALS['jp_account_errors'] = [];
        $_POST = [];
    }
    function jp_test_account_request(string $choice = 'enable'): void
    {
        $_POST = ['action' => 'save_account_details', 'save-account-details-nonce' => 'native-valid', 'jp_preferences_present' => '1', 'jp_email_marketing_choice' => $choice, 'jp_account_preferences_nonce' => 'valid'];
    }
}

namespace JouvencePara\Core\Customers {
    function get_current_user_id(): int { return $GLOBALS['jp_account_actor']; }
    function wc_get_page_id(string $page): int { return $GLOBALS['jp_account_page']; }
    function get_post_status(int $id): string { return $GLOBALS['jp_account_page_status']; }
    function get_post(int $id): \WP_Post
    {
        return new \WP_Post(['ID' => $id, 'post_type' => $GLOBALS['jp_account_page_type'], 'post_status' => $GLOBALS['jp_account_page_status'], 'post_content' => $GLOBALS['jp_account_page_content']]);
    }
    function has_shortcode(string $content, string $tag): bool { return str_contains($content, '[' . $tag . ']'); }
    function do_action(string $hook, string $code): void { $GLOBALS['jp_account_errors'][] = [$hook, $code]; }
    function wp_verify_nonce(string $nonce, string $action): bool
    {
        if ($action === 'save_account_details') { return $nonce === 'native-valid' && $GLOBALS['jp_account_native_nonce_valid']; }
        if (str_starts_with($action, 'jp_wishlist_')) { return $nonce === 'valid' && ($GLOBALS['jp_wishlist_nonce_valid'] ?? true); }
        return $nonce === 'valid' && $action === 'jp_account_preferences_' . $GLOBALS['jp_account_actor'] && $GLOBALS['jp_account_nonce_valid'];
    }
    function wp_nonce_field(string $action, string $name): void { $GLOBALS['jp_account_fields']['nonce'] = [$action, $name]; }
    function woocommerce_form_field(string $key, array $args, string $value): void { $GLOBALS['jp_account_fields'][$key] = [$args, $value]; }
    function wc_notice_count(string $type): int { return count($GLOBALS['jp_account_notices'][$type] ?? []); }
    function wc_add_notice(string $message, string $type): void { $GLOBALS['jp_account_notices'][$type][] = $message; }
}

namespace JouvencePara\Theme {
    function wc_get_page_id(string $page): int { return $GLOBALS['jp_account_page']; }
    function wc_get_page_permalink(string $page): string { return $GLOBALS['jp_account_url'] ?? 'https://store.example/mon-compte/'; }
    function get_post_status(int $id): string { return $GLOBALS['jp_test_product_statuses'][$id] ?? $GLOBALS['jp_account_page_status']; }
}
