<?php

declare(strict_types=1);

namespace App\Task\Entity;

use App\Task\Enum\RecurrenceType;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(name: 'start_date', type: 'date_immutable')]
    private DateTimeInterface $startDate;

    #[ORM\Column(name: 'end_date', type: 'date_immutable' , nullable: true)]
    private ?DateTimeInterface $endDate;

    /**
     * @var Collection<int, TaskPostpone>
     */
    #[ORM\OneToMany(mappedBy: 'recurrence', targetEntity: TaskPostpone::class)]
    private Collection $postpones;

    #[ORM\Column(name: 'deleted_at', type: 'datetime_immutable' , nullable: true)]
    private ?DateTimeInterface $deletedAt;

    public function __construct()
    {
        $this->postpones = new ArrayCollection();
    }

    abstract public function getRecurrenceType(): RecurrenceType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getStartDate(): DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getPostpones(): Collection
    {
        return $this->postpones;
    }

    public function setPostpones(Collection $postpones): self
    {
        $this->postpones = $postpones;

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
