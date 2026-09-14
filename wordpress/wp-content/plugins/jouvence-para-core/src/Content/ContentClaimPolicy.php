<?php

declare(strict_types=1);

namespace JouvencePara\Core\Content;

final class ContentClaimPolicy
{
    /** @return list<string> */
    public static function prohibitedPatterns(): array
    {
        return [
            '/\bgu[ée]rit\b/ui',
            '/\bdiagnostiqu(?:e|er|erait)\b/ui',
            '/\b100\s*%\s*(?:efficace|garanti)\b/ui',
            '/\br[ée]sultat(?:s)?\s+garanti(?:s)?\b/ui',
            '/\bgu[ée]rison\s+garantie\b/ui',
        ];
    }

    /** @return list<string> */
    public static function violations(string $text): array
    {
        $violations = [];
        foreach (self::prohibitedPatterns() as $pattern) {
            if (preg_match($pattern, wp_strip_all_tags($text)) === 1) {
                $violations[] = $pattern;
            }
        }
        return $violations;
    }

    public static function publishReady(string $sourceReference, bool $reviewApproved, int $reviewerId, string $content): bool
    {
        return trim($sourceReference) !== ''
            && $reviewApproved
            && $reviewerId > 0
            && self::violations($content) === [];
    }
}
