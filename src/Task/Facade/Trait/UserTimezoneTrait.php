<?php

declare(strict_types=1);

namespace App\Task\Facade\Trait;

use App\Context\UserContext;
use App\Locale\Entity\Timezone;
use App\UserPreference\Provider\UserPreferenceProvider;
use Symfony\Contracts\Service\Attribute\Required;

trait UserTimezoneTrait
{
    private UserPreferenceProvider $preferenceProvider;
    private UserContext $userContext;

    private function getUserTimezone(): Timezone
    {
        return $this->preferenceProvider->getTimezone($this->userContext->getUser())->getTimezone();
    }

    #[Required]
    public function setTimezoneDependencies(
        UserPreferenceProvider $preferenceProvider,
        UserContext $userContext,
    ): void
    {
        $this->preferenceProvider = $preferenceProvider;
        $this->userContext = $userContext;
    }
}
