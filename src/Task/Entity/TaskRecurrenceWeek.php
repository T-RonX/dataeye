<?php

declare(strict_types=1);

namespace App\Task\Entity;

use App\Task\Contract\RecurrenceIntervalInterface;
use App\Task\Enum\Day;
use App\Task\Enum\RecurrenceType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskRecurrenceWeek extends TaskRecurrence implements RecurrenceIntervalInterface
{
    #[ORM\Column(name: 'interv', type: 'smallint')]
    private int $interval;

    #[ORM\Column(type: 'ascii_string', length: 13)]
    private string $days;

    public function setInterval(int $interval): self
    {
        $this->interval = $interval;

        return $this;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }

    /**
     * @param array<Day> $days
     */
    public function setDays(array $days): self
    {
        $this->days = implode(',', array_map(static fn(Day $day) => $day->value, $days));

        return $this;
    }

    /**
     * @return array<Day>
     */
    public function getDays(): array
    {
        return array_map(static fn(int $day) => Day::from($day), explode(',', $this->days));
    }

    public function getRecurrenceType(): RecurrenceType
    {
        return RecurrenceType::Week;
    }
}
