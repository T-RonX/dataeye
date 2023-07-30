<?php

declare(strict_types=1);

namespace App\Facade;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.facade.context')]
interface FacadeContextInterface
{
    public function getOptionName(): string;

    public function getOptionShortcut(): string;

    public function getOptionDescription(): string;

    public function getOptionMode(): int;

    public function getOptionDefaultValue(): mixed;

    public function setContext(mixed $value): void;
}
