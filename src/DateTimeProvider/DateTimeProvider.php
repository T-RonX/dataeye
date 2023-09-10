<?php

declare(strict_types=1);

namespace App\DateTimeProvider;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

class DateTimeProvider
{
    public function getNow(DateTimeZone $timezone = null): DateTimeImmutable
    {
        return CarbonImmutable::now($timezone);
    }

    public function getNowMutable(): DateTime
    {
        return Carbon::now();
    }

    public function create(string $format, ?DateTimeZone $timezone = null): DateTimeImmutable
    {
        return CarbonImmutable::parse($format, $timezone);
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
            default => $dateTime,
        };
    }

    public function resolveDateTimeZone(string|DateTimeZone $timezone): DateTimeZone
    {
        return match (true) {
            is_string($timezone) => $this->createTimezone($timezone),
            default => $timezone,
        };
    }

    public function asDateImmutableUTC(DateTimeInterface $utcDateTime, DateTimeZone $localTimezone): DateTimeImmutable
    {
        $dateTimeLocal = DateTime::createFromInterface($utcDateTime)->setTimezone($localTimezone);

        return $this->create($dateTimeLocal->format('Y-m-d'), $this->createTimezoneUTC());
    }

    public function createTimezoneUTC(): DateTimeZone
    {
        return $this->createTimezone('UTC');
    }

    public function createTimezone(string $timezone): DateTimeZone
    {
        return new DateTimeZone($timezone);
    }

    public function changeTimeZone(DateTimeInterface $date, DateTimeZone $timezone): DateTimeInterface
    {
        return new DateTimeImmutable($date->format('Y-m-d H:i:s.u'), $timezone);
    }
}
