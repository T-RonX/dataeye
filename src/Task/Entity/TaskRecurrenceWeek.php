<?php

declare(strict_types=1);

namespace App\Task\Entity;

use App\Task\Enum\Day;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskRecurrenceWeek extends TaskRecurrence
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
     * @param array<string> $days
     */
    public function setDays(array $days): self
    {
        $this->days = implode(',', array_map(static fn(Day $day) => $day->value, $days));

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getDays(): array
    {
        return array_map(static fn(int $day) => Day::from($day), explode(',', $this->days));
    }
}
