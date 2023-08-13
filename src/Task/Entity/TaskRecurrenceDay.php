<?php

declare(strict_types=1);

namespace App\Task\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskRecurrenceDay extends TaskRecurrence
{
    #[ORM\Column(name: 'interv', type: 'smallint')]
    private int $interval;

    public function setInterval(int $interval): self
    {
        $this->interval = $interval;

        return $this;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }
}
