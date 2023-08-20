<?php

declare(strict_types=1);

namespace App\DateTimeProvider;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

class DateTimeProvider
{
    public function getNow(): DateTimeImmutable
    {
        return CarbonImmutable::now();
    }

    public function getNowMutable(): DateTime
    {
        return Carbon::now();
    }

    public function create(string $format): DateTimeImmutable
    {
        return CarbonImmutable::parse($format);
    }

    public function createMutable(string $format): DateTime
    {
        return Carbon::parse($format);
    }

    public function resolveNullableDateTime(DateTimeInterface|string|null $dateTime): ?DateTimeInterface
    {
        return match (true) {
            $dateTime === null => null,
            default => $this->resolveDateTime($dateTime),
        };
    }

    public function resolveDateTime(DateTimeInterface|string $dateTime): DateTimeInterface
    {
        return match (true) {
            is_string($dateTime) => $this->create($dateTime),
            default => $dateTime
        };
    }
}
