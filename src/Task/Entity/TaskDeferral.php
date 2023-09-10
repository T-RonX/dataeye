<?php

declare(strict_types=1);

namespace App\Task\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'smallint')]
#[ORM\DiscriminatorMap([
    1 => TaskPostpone::class,
    2 => TaskSkip::class,
])]
abstract class TaskDeferral
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'deferrals')]
    #[ORM\JoinColumn(name: 'task', referencedColumnName: 'id', nullable: false)]
    private Task $task;

    #[ORM\ManyToOne(targetEntity: TaskRecurrence::class)]
    #[ORM\JoinColumn(name: 'recurrence_id', referencedColumnName: 'id', nullable: true)]
    private ?TaskRecurrence $recurrence;

    #[ORM\ManyToOne(targetEntity: TaskParticipant::class)]
    #[ORM\JoinColumn(name: 'deferred_by', referencedColumnName: 'id', nullable: false)]
    private TaskParticipant $deferredBy;

    #[ORM\Column(name: 'deferred_at', type: 'datetime_immutable')]
    private DateTimeImmutable $deferredAt;
    
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

    public function getRecurrence(): ?TaskRecurrence
    {
        return $this->recurrence;
    }

    public function setRecurrence(?TaskRecurrence $recurrence): self
    {
        $this->recurrence = $recurrence;

        return $this;
    }

    public function getDeferredBy(): TaskParticipant
    {
        return $this->deferredBy;
    }

    public function setDeferredBy(TaskParticipant $deferredBy): self
    {
        $this->deferredBy = $deferredBy;

        return $this;
    }

    public function getDeferredAt(): DateTimeImmutable
    {
        return $this->deferredAt;
    }

    public function setDeferredAt(DateTimeImmutable $deferredAt): self
    {
        $this->deferredAt = $deferredAt;

        return $this;
    }
}
