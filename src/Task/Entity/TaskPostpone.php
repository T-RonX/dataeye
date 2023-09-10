<?php

declare(strict_types=1);

namespace App\Task\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskPostpone extends TaskDeferral
{
    #[ORM\Column(type: 'datetime_immutable', options: ['secondPrecision' => true])]
    private ?DateTimeInterface $delayedTo;

    public function getDelayedTo(): DateTimeInterface
    {
        return $this->delayedTo;
    }

    public function setDelayedTo(DateTimeInterface $delay): self
    {
        $this->delayedTo = $delay;

        return $this;
    }
}
