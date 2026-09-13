<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

final class ProductValidationResult
{
    /** @var list<string> */
    private array $hardErrors = [];

    /** @var list<string> */
    private array $publicationErrors = [];

    public function addHardError(string $code): void
    {
        if (! in_array($code, $this->hardErrors, true)) {
            $this->hardErrors[] = $code;
        }
    }

    public function addPublicationError(string $code): void
    {
        if (! in_array($code, $this->publicationErrors, true)) {
            $this->publicationErrors[] = $code;
        }
    }

    /** @return list<string> */
    public function hardErrors(): array
    {
        return $this->hardErrors;
    }

    /** @return list<string> */
    public function publicationErrors(): array
    {
        return $this->publicationErrors;
    }

    /** @return list<string> */
    public function allErrors(): array
    {
        return array_values(array_unique(array_merge($this->hardErrors, $this->publicationErrors)));
    }

    public function hasHardErrors(): bool
    {
        return $this->hardErrors !== [];
    }

    public function hasHardError(string $code): bool
    {
        return in_array($code, $this->hardErrors, true);
    }

    public function publicationReady(): bool
    {
        return ! $this->hasHardErrors() && $this->publicationErrors === [];
    }
}
