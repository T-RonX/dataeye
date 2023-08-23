<?php

declare(strict_types=1);

namespace App\Doctrine\SoftDeletable;

use DateTimeInterface;

interface SoftDeletableInterface
{
    public function getDeletedAt(): DateTimeInterface;

    public function setDeletedAt(DateTimeInterface $deletedAt): self;
}
