<?php

declare(strict_types=1);

namespace JouvencePara\Core\Discovery;

final class FilterState
{
    /** @var array<string, string> */
    public const TAXONOMIES = [
        'brand' => 'jp_brand',
        'need' => 'jp_need',
        'skin' => 'pa_skin_type',
        'hair' => 'pa_hair_type',
        'spf' => 'pa_spf',
        'audience' => 'pa_target_audience',
    ];

    /** @param array<string, list<string>> $values */
    public function __construct(private readonly array $values = [])
    {
    }

    /** @param array<string, mixed> $request */
    public static function fromRequest(array $request): self
    {
        $values = [];
        foreach (self::TAXONOMIES as $key => $taxonomy) {
            unset($taxonomy);
            $raw = $request['jp_filter_' . $key] ?? [];
            $items = is_array($raw) ? $raw : explode(',', (string) $raw);
            $clean = [];
            foreach (array_slice($items, 0, 10) as $item) {
                $slug = self::slug((string) $item);
                if ($slug !== '') {
                    $clean[$slug] = true;
                }
            }
            if ($clean !== []) {
                $values[$key] = array_keys($clean);
            }
        }
        return new self($values);
    }

    /** @return array<string, list<string>> */
    public function values(): array
    {
        return $this->values;
    }

    /** @return list<string> */
    public function group(string $key): array
    {
        return $this->values[$key] ?? [];
    }

    /** @return array<int|string, mixed> */
    public function taxQuery(?string $excludeGroup = null): array
    {
        $clauses = ['relation' => 'AND'];
        foreach (self::TAXONOMIES as $key => $taxonomy) {
            if ($key === $excludeGroup || ($this->values[$key] ?? []) === []) {
                continue;
            }
            $clauses[] = [
                'taxonomy' => $taxonomy,
                'field' => 'slug',
                'terms' => $this->values[$key],
                'operator' => 'IN',
            ];
        }
        return $clauses;
    }

    private static function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9_-]+/', '', $value) ?? '';
        return substr($value, 0, 100);
    }
}
