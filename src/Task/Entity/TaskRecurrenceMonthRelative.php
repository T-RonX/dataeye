<?php

declare(strict_types=1);

namespace App\Task\Entity;

use App\Task\Enum\Day;
use App\Task\Enum\WeekOrdinal;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskRecurrenceMonthRelative extends TaskRecurrence
{
    #[ORM\Column(type: 'smallint')]
    private int $weekOrdinal;

    #[ORM\Column(type: 'smallint')]
    private int $day;

    public function setWeekOrdinal(WeekOrdinal $weekOrdinal): self
    {
        $this->weekOrdinal = $weekOrdinal->value;

        return $this;
    }

    public function getWeekOrdinal(): WeekOrdinal
    {
        return WeekOrdinal::from($this->weekOrdinal);
    }

    public function setDay(Day $day): self
    {
        $this->day = $day->value;

        return $this;
    }

    public function getDay(): Day
    {
        return Day::from($this->day);
    }
}
