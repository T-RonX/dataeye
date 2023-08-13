<?php

declare(strict_types=1);

namespace App\Task\Entity;

use App\Task\Repository\TaskRepository;
use App\User\Entity\User;
use App\Uuid\Entity\EntityUuidInterface;
use App\Uuid\Entity\EntityUuidTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task implements EntityUuidInterface
{
    use EntityUuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(length: 1000, nullable: true)]
    private string $description;

    #[ORM\Column(nullable: true)]
    private int $duration;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owned_by', referencedColumnName: 'id', nullable: false)]
    private User $ownedBy;

    #[ORM\ManyToOne(targetEntity: TaskCategory::class)]
    #[ORM\JoinColumn(name: 'category', referencedColumnName: 'id', nullable: true)]
    private ?TaskCategory $category;

    /**
     * @var Collection<int, TaskRecurrence>
     */
    #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskRecurrence::class)]
    private Collection $recurrences;

    /**
     * @var Collection<int, TaskParticipant>
     */
    #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskParticipant::class)]
    private Collection $participants;

    /**
     * @var Collection<int, TaskCompletion>
     */
    #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskCompletion::class)]
    private Collection $completions;

    /**
     * @var Collection<int, TaskPostpone>
     */
    #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskPostpone::class)]
    private Collection $postpones;

    public function __construct()
    {
        $this->recurrences = new ArrayCollection();
        $this->participants = new ArrayCollection();
        $this->completions = new ArrayCollection();
        $this->postpones = new ArrayCollection();
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
    
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
    
    public function getDuration(): int
    {
        return $this->duration;
    }
    
    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getOwnedBy(): User
    {
        return $this->ownedBy;
    }

    public function setOwnedBy(User $ownedBy): self
    {
        $this->ownedBy = $ownedBy;

        return $this;
    }
    
    public function getCategory(): ?TaskCategory
    {
        return $this->category;
    }
    
    public function setCategory(?TaskCategory $category): self
    {
        $this->category = $category;

        return $this;
    }
    
    public function getRecurrences(): Collection
    {
        return $this->recurrences;
    }
    
    public function setRecurrences(Collection $recurrences): self
    {
        $this->recurrences = $recurrences;

        return $this;
    }
    
    public function getParticipants(): Collection
    {
        return $this->participants;
    }
    
    public function setParticipants(Collection $participants): self
    {
        $this->participants = $participants;

        return $this;
    }
    
    public function getCompletions(): Collection
    {
        return $this->completions;
    }
    
    public function setCompletions(Collection $completions): self
    {
        $this->completions = $completions;

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
}
