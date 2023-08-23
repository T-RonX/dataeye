<?php

declare(strict_types=1);

namespace App\UserPreference\Entity;

use App\Locale\Entity\Timezone;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserPreferenceTimezone extends UserPreference
{
    #[ORM\ManyToOne(targetEntity: Timezone::class)]
    #[ORM\JoinColumn(referencedColumnName: "id", nullable: false)]
    private Timezone $timezone;

    public function getTimezone(): Timezone
    {
        return $this->timezone;
    }

    public function setTimezone(Timezone $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }
}
