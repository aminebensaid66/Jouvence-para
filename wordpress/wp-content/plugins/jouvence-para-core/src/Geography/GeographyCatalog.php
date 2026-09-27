<?php

declare(strict_types=1);

namespace JouvencePara\Core\Geography;

final class GeographyCatalog
{
    /** @var array<string, GeographyNode> */
    private array $nodes = [];

    /** @param iterable<GeographyNode> $nodes */
    public function __construct(iterable $nodes)
    {
        foreach ($nodes as $node) {
            if (isset($this->nodes[$node->id])) {
                throw new \InvalidArgumentException('Duplicate geography ID: ' . $node->id);
            }
            $this->nodes[$node->id] = $node;
        }
        foreach ($this->nodes as $node) {
            if ($node->level !== GeographyNode::GOVERNORATE && ($node->parentId === null || ! isset($this->nodes[$node->parentId]))) {
                throw new \InvalidArgumentException('Missing geography parent for ' . $node->id);
            }
        }
    }

    /** @return list<GeographyNode> */
    public function byLevel(string $level): array
    {
        return array_values(array_filter($this->nodes, static fn (GeographyNode $node): bool => $node->level === $level));
    }

    /** @return list<GeographyNode> */
    public function children(string $parentId): array
    {
        return array_values(array_filter($this->nodes, static fn (GeographyNode $node): bool => $node->parentId === $parentId));
    }

    public function has(string $id): bool
    {
        return isset($this->nodes[$id]);
    }

    public function validPath(string $governorateId, ?string $delegationId = null, ?string $localityId = null): bool
    {
        $governorate = $this->nodes[$governorateId] ?? null;
        if (! $governorate || $governorate->level !== GeographyNode::GOVERNORATE) {
            return false;
        }
        if ($delegationId === null || $delegationId === '') {
            return $localityId === null || $localityId === '';
        }
        $delegation = $this->nodes[$delegationId] ?? null;
        if (! $delegation || $delegation->level !== GeographyNode::DELEGATION || $delegation->parentId !== $governorateId) {
            return false;
        }
        if ($localityId === null || $localityId === '') {
            return true;
        }
        $locality = $this->nodes[$localityId] ?? null;
        return $locality instanceof GeographyNode
            && $locality->level === GeographyNode::LOCALITY
            && $locality->parentId === $delegationId;
    }
}
