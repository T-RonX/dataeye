<?php

declare(strict_types=1);

namespace App\AuditLog\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AuditLogFieldUpdate extends AuditLogField
{
    #[ORM\Column(name: 'new_value')]
    private string $newValue;

    #[ORM\Column(name: 'old_value', nullable: true)]
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
