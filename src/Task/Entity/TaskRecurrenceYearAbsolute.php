<?php

declare(strict_types=1);

namespace App\Task\Entity;

use App\Task\Enum\Month;
use App\Task\Enum\RecurrenceType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskRecurrenceYearAbsolute extends TaskRecurrence
{
    #[ORM\Column(type: 'smallint')]
    private int $month;

    #[ORM\Column(type: 'smallint')]
    private int $dayNumber;

    public function setMonth(Month $month): self
    {
        $this->month = $month->value;

        return $this;
    }

    public function getMonth(): Month
    {
        return Month::from($this->month);
    }

    public function setDayNumber(int $dayNumber): self
    {
        $this->dayNumber = $dayNumber;

        return $this;
    }

    public function getDayNumber(): int
    {
        return $this->dayNumber;
    }

    public function getRecurrenceType(): RecurrenceType
    {
        return RecurrenceType::Year;
    }
}
