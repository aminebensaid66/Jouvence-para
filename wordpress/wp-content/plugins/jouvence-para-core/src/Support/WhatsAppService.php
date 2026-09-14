<?php

declare(strict_types=1);

namespace JouvencePara\Core\Support;

final class WhatsAppService
{
    public const DISPLAY_NUMBER = '+216 29 302 202';
    public const WA_NUMBER = '21629302202';

    public function globalUrl(): string
    {
        return 'https://wa.me/' . self::WA_NUMBER;
    }

    public function productUrl(string $productName, string $canonicalUrl): string
    {
        $message = sprintf('Bonjour Jouvence Para, je souhaite un conseil sur %s : %s', trim($productName), trim($canonicalUrl));
        return $this->globalUrl() . '?text=' . rawurlencode($message);
    }
}
