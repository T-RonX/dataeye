<?php

declare(strict_types=1);

namespace App\AuditLog\Entity;

use App\AuditLog\Repository\AuditLogEntityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditLogEntityRepository::class)]
class AuditLogEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column]
    private string $entity;

    public function setEntity(string $entity): self
    {
        $this->entity = $entity;

        return $this;
    }
}
