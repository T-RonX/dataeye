<?php

declare(strict_types=1);

namespace App\Task\Entity;

use App\Task\Enum\RecurrenceType;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'smallint')]
#[ORM\DiscriminatorMap([
    1 => TaskRecurrenceDay::class,
    2 => TaskRecurrenceWeek::class,
    3 => TaskRecurrenceMonthAbsolute::class,
    4 => TaskRecurrenceMonthRelative::class,
    5 => TaskRecurrenceYearAbsolute::class,
    6 => TaskRecurrenceYearRelative::class,
])]
abstract class TaskRecurrence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'recurrences')]
    #[ORM\JoinColumn(name: 'task', referencedColumnName: 'id', nullable: false)]
    private Task $task;

    #[ORM\Column(name: 'starts_at', type: 'datetimetz')]
    private DateTimeInterface $startsAt;

    #[ORM\Column(name: 'ends_at', type: 'datetimetz' , nullable: true)]
    private ?DateTimeInterface $endsAt;

    #[ORM\Column(name: 'deleted_at', type: 'datetimetz' , nullable: true)]
    private ?DateTimeInterface $deletedAt;

    abstract public function getRecurrenceType(): RecurrenceType;

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

    public function getStartsAt(): DateTimeInterface
    {
        return $this->startsAt;
    }

    public function setStartsAt(DateTimeInterface $startsAt): self
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): ?DateTimeInterface
    {
        return $this->endsAt;
    }

    public function setEndsAt(?DateTimeInterface $endsAt): self
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
