<?php

declare(strict_types=1);

namespace App\Task\Entity;

use App\Task\Contract\RecurrenceIntervalInterface;
use App\Task\Enum\RecurrenceType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskRecurrenceDay extends TaskRecurrence implements RecurrenceIntervalInterface
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

    public function getRecurrenceType(): RecurrenceType
    {
        return RecurrenceType::Day;
    }
}
