<?php

declare(strict_types=1);

namespace App\Facade;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.facade')]
interface FacadeInterface
{
}