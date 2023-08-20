<?php

declare(strict_types=1);

namespace App\Collection;

readonly class CollectionUpdate
{
    public function __construct(
        private array $added,
        private array $removed
    ) {
    }

    public function getAdded(): array
    {
        return $this->added;
    }

    public function getRemoved(): array
    {
        return $this->removed;
    }
}