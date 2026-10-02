<?php

declare(strict_types=1);

namespace JouvencePara\Core\Support;

final class WhatsAppService
{
    public const DISPLAY_NUMBER = '+216 29 302 202';
    public const WA_NUMBER = '21629302202';
    private const MAX_SHARED_ITEMS = 10;

    public function globalUrl(): string
    {
        return 'https://wa.me/' . self::WA_NUMBER;
    }

    public function productUrl(string $productName, string $canonicalUrl): string
    {
        $message = sprintf('Bonjour Jouvence Para, je souhaite un conseil sur %s : %s', trim($productName), trim($canonicalUrl));
        return $this->globalUrl() . '?text=' . rawurlencode($message);
    }

    /** @param list<array{name: string, url: string, quantity: int}> $items */
    public function cartUrl(array $items, int $unavailableCount = 0): string
    {
        $lines = ['Bonjour Jouvence Para, pouvez-vous me renseigner sur les produits de mon panier ?'];
        $shared = 0;
        $omittedCount = max(0, $unavailableCount);
        foreach ($items as $item) {
            if (! is_array($item) || ! is_string($item['name'] ?? null) || ! is_string($item['url'] ?? null)
                || ! is_int($item['quantity'] ?? null) || $item['quantity'] < 1
                || ! $this->isPublicHttpUrl($item['url'])) {
                ++$omittedCount;
                continue;
            }
            if ($shared >= self::MAX_SHARED_ITEMS) {
                ++$omittedCount;
                continue;
            }
            $name = trim(strip_tags($item['name']));
            $name = preg_replace('/[\p{C}\s]+/u', ' ', $name) ?? '';
            $name = trim($name);
            if ($name === '') {
                ++$omittedCount;
                continue;
            }
            if (function_exists('mb_substr')) {
                $name = mb_substr($name, 0, 100, 'UTF-8');
            } else {
                preg_match_all('/./us', $name, $characters);
                $name = implode('', array_slice($characters[0] ?? [], 0, 100));
            }
            $lines[] = sprintf('- %d × %s : %s', $item['quantity'], $name, trim($item['url']));
            ++$shared;
        }
        if ($shared === 0) {
            return '';
        }
        if ($omittedCount > 0) {
            $lines[] = sprintf('- %d autre(s) article(s) non inclus car indisponible(s) ou hors limite.', $omittedCount);
        }
        $lines[] = 'Prix et disponibilité à confirmer avant commande.';
        return $this->globalUrl() . '?text=' . rawurlencode(implode("\n", $lines));
    }

    private function isPublicHttpUrl(string $url): bool
    {
        if (strlen($url) > 500) {
            return false;
        }
        $parts = parse_url($url);
        if (! is_array($parts) || ! in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
            || ! is_string($parts['host'] ?? null) || $parts['host'] === ''
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) {
            return false;
        }
        if (function_exists('home_url')) {
            $site = parse_url(home_url('/'));
            if (! is_array($site) || strtolower((string) ($site['scheme'] ?? '')) !== strtolower((string) $parts['scheme'])
                || strtolower((string) ($site['host'] ?? '')) !== strtolower($parts['host'])
                || (int) ($site['port'] ?? 0) !== (int) ($parts['port'] ?? 0)) {
                return false;
            }
        }
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
            $allowed = ['p', 'post_type', 'product'];
            if (array_diff(array_keys($query), $allowed) !== []) {
                return false;
            }
            foreach ($query as $value) {
                if (! is_string($value)) {
                    return false;
                }
            }
            if ((isset($query['p']) && (! ctype_digit($query['p']) || (int) $query['p'] < 1))
                || (isset($query['post_type']) && $query['post_type'] !== 'product')
                || (isset($query['product']) && preg_match('/\A[a-z0-9_-]+\z/iD', $query['product']) !== 1)) {
                return false;
            }
        }
        return true;
    }
}
