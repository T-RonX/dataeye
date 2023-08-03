<?php

declare(strict_types=1);

namespace App\AuditLog\Entity;

use App\AuditLog\EntityModeEnum;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: AuditLogEntity::class)]
    #[ORM\JoinColumn(name: "audit_log_entity", referencedColumnName: "id", nullable: false)]
    private AuditLogEntity $entity;

    #[ORM\Column]
    private int $entityId;

    #[ORM\Column]
    private int $mode;

    #[ORM\Column]
    private DateTime $date;

    public function setEntity(AuditLogEntity $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function setEntityId(int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function setMode(EntityModeEnum $mode): self
    {
        $this->mode = $mode->value;

        return $this;
    }

    public function setDate(DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }
}
