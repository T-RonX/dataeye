<?php

declare(strict_types=1);

namespace App\AuditLog\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AuditLogFieldUpdate extends AuditLogField
{
    #[ORM\Column]
    private string $newValue;

    #[ORM\Column(nullable: true)]
    private ?string $oldValue;

    public function setNewValue(string $newValue): self
    {
        $this->newValue = $newValue;

        return $this;
    }

    public function setOldValue(string $oldValue): self
    {
        $this->oldValue = $oldValue;

        return $this;
    }
}
