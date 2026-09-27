<?php

declare(strict_types=1);

namespace JouvencePara\Core\Discovery;

final class SortPolicy
{
    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            'relevance' => 'Pertinence',
            'bestseller' => 'Meilleures ventes',
            'newest' => 'Nouveautés',
            'price' => 'Prix croissant',
            'price-desc' => 'Prix décroissant',
        ];
    }

    public static function normalize(string $value): string
    {
        return isset(self::options()[$value]) ? $value : 'relevance';
    }

    /** @param array<string, mixed> $args @return array<string, mixed> */
    public static function orderingArgs(array $args, string $orderby): array
    {
        return match (self::normalize($orderby)) {
            'bestseller' => $args + [],
            'newest' => array_replace($args, ['orderby' => 'date ID', 'order' => 'DESC']),
            'relevance' => array_replace($args, ['orderby' => 'menu_order title ID', 'order' => 'ASC']),
            default => $args,
        };
    }
}
