<?php

declare(strict_types=1);

namespace App\Task\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskRecurrenceMonthAbsolute extends TaskRecurrence
{
    #[ORM\Column(type: 'smallint')]
    private int $dayNumber;

    public function setDayNumber(int $dayNumber): self
    {
        $this->dayNumber = $dayNumber;

        return $this;
    }

    public function getDayNumber(): int
    {
        return $this->dayNumber;
    }
}
