<?php

declare(strict_types=1);

namespace App\Task\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskPostpone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'postpones')]
    #[ORM\JoinColumn(name: 'task', referencedColumnName: 'id', nullable: false)]
    private Task $task;

    #[ORM\ManyToOne(targetEntity: TaskParticipant::class)]
    #[ORM\JoinColumn(name: 'postponed_by', referencedColumnName: 'id', nullable: false)]
    private TaskParticipant $postponedBy;

    #[ORM\Column(name: 'postponed_at', type: 'datetimetz_immutable')]
    private DateTimeImmutable $postponedAt;
    
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

    public function getPostponedBy(): TaskParticipant
    {
        return $this->postponedBy;
    }

    public function setPostponedBy(TaskParticipant $postponedBy): self
    {
        $this->postponedBy = $postponedBy;

        return $this;
    }
    
    public function getPostponedAt(): DateTimeImmutable
    {
        return $this->postponedAt;
    }

    public function setPostponedAt(DateTimeImmutable $postponedAt): self
    {
        $this->postponedAt = $postponedAt;

        return $this;
    }
}
