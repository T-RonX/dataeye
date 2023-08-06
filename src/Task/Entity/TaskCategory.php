<?php

declare(strict_types=1);

namespace App\Task\Entity;

use App\User\Entity\User;
use App\Uuid\Entity\EntityUuidInterface;
use App\Uuid\Entity\EntityUuidTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskCategory implements EntityUuidInterface
{
    use EntityUuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owned_by', referencedColumnName: 'id', nullable: false)]
    private User $ownedBy;

    #[ORM\Column]
    private string $name;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
    
    public function getOwnedBy(): User
    {
        return $this->ownedBy;
    }
    
    public function setOwnedBy(User $ownedBy): self
    {
        $this->ownedBy = $ownedBy;

        return $this;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
