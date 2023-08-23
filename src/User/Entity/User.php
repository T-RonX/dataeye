<?php

declare(strict_types=1);

namespace App\User\Entity;

use App\User\Repository\UserRepository;
use App\UserPreference\Entity\UserPreference;
use App\Uuid\Entity\EntityUuidInterface;
use App\Uuid\Entity\EntityUuidTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements EntityUuidInterface, UserInterface, PasswordAuthenticatedUserInterface
{
    use EntityUuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 200)]
    private string $username;

    #[ORM\Column(length: 60)]
    private string $password;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'associatedWith')]
    private Collection $associatedTo;

    /**
     * @var Collection<int, User>
     */
    #[ORM\JoinTable(name: 'user_associates')]
    #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'associate', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'associatedTo')]
    private Collection $associatedWith;

    /**
     * @var Collection<int, UserPreference>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserPreference::class)]
    private Collection $preferences;

    public function __construct()
    {
        $this->associatedTo = new ArrayCollection();
        $this->associatedWith = new ArrayCollection();
        $this->preferences = new ArrayCollection();
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void
    {

    }

    public function getAssociatedTo(): Collection
    {
        return $this->associatedTo;
    }

    public function setAssociatedTo(Collection $associatedTo): self
    {
        $this->associatedTo = $associatedTo;

        return $this;
    }

    public function getAssociatedWith(): Collection
    {
        return $this->associatedWith;
    }

    public function setAssociatedWith(Collection $associatedWith): self
    {
        $this->associatedWith = $associatedWith;

        return $this;
    }

    public function getPreferences(): Collection
    {
        return $this->preferences;
    }

    public function setPreferences(Collection $preferences): self
    {
        $this->preferences = $preferences;

        return $this;
    }
}