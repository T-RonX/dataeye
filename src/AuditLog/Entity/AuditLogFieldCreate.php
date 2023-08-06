<?php

declare(strict_types=1);

namespace App\AuditLog\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AuditLogFieldCreate extends AuditLogField
{
    #[ORM\Column(nullable: true)]
    private ?string $value;

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
