<?php

declare(strict_types=1);

namespace App\Task\Entity;

use App\Task\Enum\Day;
use App\Task\Enum\DayOrdinal;
use App\Task\Enum\Month;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskRecurrenceYearRelative extends TaskRecurrence
{
    #[ORM\Column(type: 'smallint')]
    private int $dayOrdinal;

    #[ORM\Column(type: 'smallint')]
    private int $month;

    #[ORM\Column(type: 'smallint')]
    private int $day;

    public function getDayOrdinal(): DayOrdinal
    {
        return DayOrdinal::from($this->dayOrdinal);
    }

    public function setDayOrdinal(DayOrdinal $dayOrdinal): self
    {
        $this->dayOrdinal = $dayOrdinal->value;

        return $this;
    }

    public function setMonth(Month $month): self
    {
        $this->month = $month->value;

        return $this;
    }

    public function getMonth(): Month
    {
        return Month::from($this->month);
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
