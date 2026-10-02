<?php

declare(strict_types=1);

namespace JouvencePara\Core\Privacy;

use JouvencePara\Core\Contracts\Module;
use JouvencePara\Core\Customers\CommunicationPreferences;
use JouvencePara\Core\Customers\WishlistPolicy;

final class CustomerDataRightsModule implements Module
{
    private const WISHLIST_META = '_jp_wishlist_product_ids';
    private const EXPORTER_ID = 'jouvence-para-customer-data';

    public function register(): void
    {
        add_filter('wp_privacy_personal_data_exporters', [$this, 'registerExporter']);
        add_filter('wp_privacy_personal_data_erasers', [$this, 'registerEraser']);
    }

    /** @param array<string, array<string, mixed>> $exporters @return array<string, array<string, mixed>> */
    public function registerExporter(array $exporters): array
    {
        $exporters[self::EXPORTER_ID] = [
            'exporter_friendly_name' => __('Jouvence Para customer data', 'jouvence-para-core'),
            'callback' => [$this, 'exportCustomerData'],
        ];
        return $exporters;
    }

    /** @param array<string, array<string, mixed>> $erasers @return array<string, array<string, mixed>> */
    public function registerEraser(array $erasers): array
    {
        $erasers[self::EXPORTER_ID] = [
            'eraser_friendly_name' => __('Jouvence Para customer data', 'jouvence-para-core'),
            'callback' => [$this, 'eraseCustomerData'],
        ];
        return $erasers;
    }

    /** @return array{data: list<array<string, mixed>>, done: bool} */
    public function exportCustomerData(string $emailAddress, int $page = 1): array
    {
        if ($page !== 1 || ! function_exists('get_user_by') || ! function_exists('get_user_meta')) {
            return ['data' => [], 'done' => true];
        }

        $user = get_user_by('email', sanitize_email($emailAddress));
        if (! is_object($user) || (int) ($user->ID ?? 0) < 1) {
            return ['data' => [], 'done' => true];
        }

        $userId = (int) $user->ID;
        $items = [];
        $preferences = get_user_meta($userId, CommunicationPreferences::META_KEY, true);
        if (CommunicationPreferences::valid($preferences)) {
            $items[] = [
                'name' => __('Email marketing preference', 'jouvence-para-core'),
                'value' => $preferences['email_marketing'] ? __('Enabled', 'jouvence-para-core') : __('Disabled', 'jouvence-para-core'),
            ];
            $items[] = [
                'name' => __('Email marketing preference updated at (UTC)', 'jouvence-para-core'),
                'value' => $preferences['updated_at'],
            ];
        }

        $productIds = WishlistPolicy::productIds(get_user_meta($userId, self::WISHLIST_META, true));
        if ($productIds !== []) {
            $items[] = [
                'name' => __('Saved product IDs', 'jouvence-para-core'),
                'value' => implode(', ', array_map('strval', $productIds)),
            ];
        }

        if ($items === []) {
            return ['data' => [], 'done' => true];
        }

        return [
            'data' => [[
                'group_id' => 'jouvence-para-customer',
                'group_label' => __('Jouvence Para customer data', 'jouvence-para-core'),
                'group_description' => __('Saved customer preferences and wishlist identifiers.', 'jouvence-para-core'),
                'item_id' => 'jouvence-para-customer-' . $userId,
                'data' => $items,
            ]],
            'done' => true,
        ];
    }

    /** @return array{items_removed: bool, items_retained: bool, messages: list<string>, done: bool} */
    public function eraseCustomerData(string $emailAddress, int $page = 1): array
    {
        if ($page !== 1 || ! function_exists('get_user_by') || ! function_exists('metadata_exists')
            || ! function_exists('delete_user_meta')) {
            return ['items_removed' => false, 'items_retained' => true, 'messages' => [], 'done' => true];
        }

        $user = get_user_by('email', sanitize_email($emailAddress));
        if (! is_object($user) || (int) ($user->ID ?? 0) < 1) {
            return ['items_removed' => false, 'items_retained' => false, 'messages' => [], 'done' => true];
        }

        $userId = (int) $user->ID;
        $removed = false;
        foreach ([self::WISHLIST_META, CommunicationPreferences::META_KEY] as $metaKey) {
            if (! metadata_exists('user', $userId, $metaKey)) {
                continue;
            }
            if (! delete_user_meta($userId, $metaKey) || metadata_exists('user', $userId, $metaKey)) {
                return [
                    'items_removed' => $removed,
                    'items_retained' => true,
                    'messages' => [__('Some Jouvence Para customer data could not be removed.', 'jouvence-para-core')],
                    'done' => true,
                ];
            }
            $removed = true;
        }

        return ['items_removed' => $removed, 'items_retained' => false, 'messages' => [], 'done' => true];
    }
}
