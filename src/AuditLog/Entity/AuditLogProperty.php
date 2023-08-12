<?php

declare(strict_types=1);

namespace App\AuditLog\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AuditLogProperty
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: AuditLog::class)]
    #[ORM\JoinColumn(referencedColumnName: "id", nullable: false)]
    private AuditLog $auditLog;

    #[ORM\Column]
    private string $name;

    #[ORM\Column]
    private string $value;

    public function setAuditLog(AuditLog $auditLog): self
    {
        $this->auditLog = $auditLog;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
