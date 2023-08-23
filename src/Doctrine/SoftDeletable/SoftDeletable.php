<?php

declare(strict_types=1);

namespace App\Doctrine\SoftDeletable;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait SoftDeletable
{
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private DateTimeInterface $deletedAt;

    public function getDeletedAt(): DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
