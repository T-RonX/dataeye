<?php

declare(strict_types=1);

namespace App\Uuid\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntityUuidTrait
{
    #[ORM\Column(length: 36, unique: true)]
    private ?string $uuid = null;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function hasUuid(): bool
    {
        return $this->uuid !== null;
    }
}
