<?php

declare(strict_types=1);

namespace App\Task\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskSkip extends TaskDeferral
{
}
