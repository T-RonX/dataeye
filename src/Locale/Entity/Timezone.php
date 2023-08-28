<?php

declare(strict_types=1);

namespace App\Locale\Entity;

use App\Locale\Repository\TimezoneRepository;
use App\Uuid\Entity\EntityUuidInterface;
use App\Uuid\Entity\EntityUuidTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimezoneRepository::class)]
#[ORM\Table(name: 'locale_timezone')]
class Timezone implements EntityUuidInterface
{
    use EntityUuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column]
    private string $name;

    #[ORM\Column]
    private string $version;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }
}
