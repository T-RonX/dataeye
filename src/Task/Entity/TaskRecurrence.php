<?php

declare(strict_types=1);

namespace App\Task\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'smallint', length: 2)]
#[ORM\DiscriminatorMap([
    1 => TaskRecurrenceDay::class,
    2 => TaskRecurrenceWeek::class,
    3 => TaskRecurrenceMonthAbsolute::class,
    4 => TaskRecurrenceMonthRelative::class,
    5 => TaskRecurrenceYearAbsolute::class,
    6 => TaskRecurrenceYearRelative::class,
])]
class TaskRecurrence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'recurrences')]
    #[ORM\JoinColumn(name: 'task', referencedColumnName: 'id', nullable: false)]
    private Task $task;

    #[ORM\Column(name: 'starts_at', type: 'datetimetz_immutable')]
    private DateTimeImmutable $startsAt;

    #[ORM\Column(name: 'ends_at', type: 'datetimetz_immutable' , nullable: true)]
    private ?DateTimeImmutable $endsAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function setTask(Task $task): self
    {
        $this->task = $task;

        return $this;
    }

    public function getStartsAt(): DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(DateTimeImmutable $startsAt): self
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): ?DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(?DateTimeImmutable $endsAt): self
    {
        $this->endsAt = $endsAt;

        return $this;
    }
}
