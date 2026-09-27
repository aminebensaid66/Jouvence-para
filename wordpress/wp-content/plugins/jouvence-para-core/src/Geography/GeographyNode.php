<?php

declare(strict_types=1);

namespace JouvencePara\Core\Geography;

final class GeographyNode
{
    public const GOVERNORATE = 'governorate';
    public const DELEGATION = 'delegation';
    public const LOCALITY = 'locality';

    public function __construct(
        public readonly string $id,
        public readonly string $level,
        public readonly string $labelFr,
        public readonly ?string $parentId = null,
        public readonly ?string $labelAr = null
    ) {
        if ($id === '' || ! in_array($level, [self::GOVERNORATE, self::DELEGATION, self::LOCALITY], true)) {
            throw new \InvalidArgumentException('Invalid geography node.');
        }
    }
}
