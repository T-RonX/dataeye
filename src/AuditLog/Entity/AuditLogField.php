<?php

declare(strict_types=1);

namespace App\AuditLog\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AuditLogField
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: AuditLog::class)]
    #[ORM\JoinColumn(name: "audit_log", referencedColumnName: "id", nullable: false)]
    private AuditLog $auditLog;

    #[ORM\Column(name: 'field_name')]
    private string $fieldName;

    public function setAuditLog(AuditLog $auditLog): self
    {
        $this->auditLog = $auditLog;

        return $this;
    }

    public function setFieldName(string $fieldName): self
    {
        $this->fieldName = $fieldName;

        return $this;
    }
}
