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

    #[ORM\Column(type: 'smallint')]
    private int $offsetHours;

    #[ORM\Column(type: 'smallint')]
    private int $offsetMinutes;

    #[ORM\Column]
    private string $name;

    public function getOffsetHours(): int
    {
        return $this->offsetHours;
    }

    public function setOffsetHours(int $offsetHours): self
    {
        $this->offsetHours = $offsetHours;

        return $this;
    }

    public function getOffsetMinutes(): int
    {
        return $this->offsetMinutes;
    }

    public function setOffsetMinutes(int $offsetMinutes): self
    {
        $this->offsetMinutes = $offsetMinutes;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
