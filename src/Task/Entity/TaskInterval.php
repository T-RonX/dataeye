<?php

declare(strict_types=1);

namespace App\Task\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskInterval
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Task::class)]
    #[ORM\JoinColumn(name: 'task', referencedColumnName: 'id', nullable: false)]
    private Task $task;

    #[ORM\Column(name: 'starts_at')]
    private DateTime $startsAt;

    #[ORM\Column(name: 'ends_at', nullable: true)]
    private ?DateTime $endsAt;

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

    public function getStartsAt(): DateTime
    {
        return $this->startsAt;
    }

    public function setStartsAt(DateTime $startsAt): self
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): DateTime
    {
        return $this->endsAt;
    }

    public function setEndsAt(?DateTime $endsAt): self
    {
        $this->endsAt = $endsAt;

        return $this;
    }
}
