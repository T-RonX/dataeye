<?php

declare(strict_types=1);

namespace App\Task\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskCompletion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'completions')]
    #[ORM\JoinColumn(name: 'task', referencedColumnName: 'id', nullable: false)]
    private Task $task;

    #[ORM\ManyToOne(targetEntity: TaskParticipant::class)]
    #[ORM\JoinColumn(name: 'completion_by', referencedColumnName: 'id', nullable: false)]
    private TaskParticipant $completionBy;

    #[ORM\Column(type: 'datetimetz_immutable')]
    private DateTimeImmutable $completionAt;
    
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

    public function getCompletionBy(): TaskParticipant
    {
        return $this->completionBy;
    }
    
    public function setCompletionBy(TaskParticipant $completionBy): self
    {
        $this->completionBy = $completionBy;

        return $this;
    }
    
    public function getCompletionAt(): DateTimeImmutable
    {
        return $this->completionAt;
    }
    
    public function setCompletionAt(DateTimeImmutable $completionAt): self
    {
        $this->completionAt = $completionAt;

        return $this;
    }
}
