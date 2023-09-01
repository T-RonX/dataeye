<?php

declare(strict_types=1);

namespace App\AuditLog\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AuditLogCollectionInsert extends AuditLogField
{
    #[ORM\ManyToOne(targetEntity: AuditLogEntity::class)]
    #[ORM\JoinColumn(referencedColumnName: "id", nullable: false)]
    private AuditLogEntity $targetEntity;

    #[ORM\Column]
    private int $targetId;

    public function setEntity(AuditLogEntity $entity): self
    {
        $this->targetEntity = $entity;

        return $this;
    }

    public function setEntityId(int $id): self
    {
        $this->targetId = $id;

        return  $this;
    }
}
