<?php

declare(strict_types=1);

namespace App\Facades\Task\Trait;

use App\DateTimeProvider\DateTimeProvider;
use App\Task\Entity\TaskCategory;
use App\Task\Entity\TaskParticipant;
use App\Task\Enum\Day;
use App\Task\Enum\DayOrdinal;
use App\Task\Enum\Month;
use App\Task\Enum\RecurrenceField;
use App\Task\Enum\RecurrenceMode;
use App\Task\Enum\RecurrenceType;
use App\Task\Enum\WeekOrdinal;
use App\Task\Provider\TaskCategoryProvider;
use App\User\Entity\User;
use App\User\Provider\UserProvider;
use DateTimeInterface;
use DateTimeZone;
use Symfony\Contracts\Service\Attribute\Required;

trait ArgumentResolverTrait
{
    use UserTimezoneTrait;

    private DateTimeProvider $dateTimeProvider;
    private UserProvider $userProvider;
    private TaskCategoryProvider $taskCategoryProvider;

    /**
     * @return array{TaskCategory|null, TaskParticipant[], DateTimeInterface, DateTimeInterface|null, RecurrenceType|null, mixed[}
     */
    private function resolveCommonValues(TaskCategory|int|null $category, array|string $participatingUsers, DateTimeInterface|string $dateTime, DateTimeInterface|string|null $recurrenceEndDate, RecurrenceType|int|null $taskRecurrence, array|null $recurrenceParams) : array
    {
        return [
            $this->resolveCategory($category),
            $this->resolveParticipatingUsers($participatingUsers),
            $resolvedDateTime = $this->dateTimeProvider->resolveDateTime($dateTime),
            $this->resolveDateFromUTCDateTime($resolvedDateTime),
            $this->dateTimeProvider->resolveNullableDateTime($recurrenceEndDate),
            $taskRecurrence = $this->resolveTaskRecurrence($taskRecurrence),
            $this->resolveTaskRecurrenceParams($taskRecurrence, $recurrenceParams),
        ];
    }

    private function resolveParticipatingUsers(array $participatingUsers): array
    {
        return array_map(fn(User|int $participant) => $this->userProvider->resolveUser($participant), $participatingUsers);
    }

    private function resolveTaskRecurrenceParams(RecurrenceType|null $taskRecurrence, array $recurrenceParams): array
    {
        return match ($taskRecurrence) {
            RecurrenceType::Day => $this->resolveTaskRecurrenceDayParams($recurrenceParams),
            RecurrenceType::Week => $this->resolveTaskRecurrenceWeekParams($recurrenceParams),
            RecurrenceType::Month => $this->resolveTaskRecurrenceMonthParams($recurrenceParams),
            RecurrenceType::Year => $this->resolveTaskRecurrenceYearParams($recurrenceParams),
            default => [],
        };
    }

    private function resolveTaskRecurrenceDayParams(array $params): array
    {
        return [
            RecurrenceField::DayInterval->value => (int) $params[RecurrenceField::DayInterval->value],
        ];
    }

    private function resolveTaskRecurrenceWeekParams(array $params): array
    {
        return [
            RecurrenceField::WeekInterval->value => (int) $params[RecurrenceField::WeekInterval->value],
            RecurrenceField::WeekDays->value => $this->resolveDays($params[RecurrenceField::WeekDays->value]),
        ];
    }

    private function resolveTaskRecurrenceMonthParams(array $params): array
    {
        return [
            RecurrenceField::MonthMode->value => $mode = $this->resolveMode($params[RecurrenceField::MonthMode->value]),
            RecurrenceField::MonthInterval->value => (int) $params[RecurrenceField::MonthInterval->value],
            ...match ($mode) {
                RecurrenceMode::Absolute => [
                    RecurrenceField::MonthAbsoluteDayNumber->value => (int) $params[RecurrenceField::MonthAbsoluteDayNumber->value],
                ],
                RecurrenceMode::Relative => [
                    RecurrenceField::MonthRelativeWeekOrdinal->value => $this->resolveWeekOrdinal($params[RecurrenceField::MonthRelativeWeekOrdinal->value]),
                    RecurrenceField::MonthRelativeDay->value => $params[RecurrenceField::MonthRelativeDay->value],
                ],
            }];
    }

    private function resolveTaskRecurrenceYearParams(array $params): array
    {
        return [
            RecurrenceField::YearMode->value => $mode = $this->resolveMode($params[RecurrenceField::YearMode->value]),
            RecurrenceField::YearMonth->value => $this->resolveMonth($params[RecurrenceField::YearMonth->value]),
            ...match ($mode) {
                RecurrenceMode::Absolute => [
                    RecurrenceField::YearAbsoluteDayNumber->value => (int) $params[RecurrenceField::YearAbsoluteDayNumber->value],
                ],
                RecurrenceMode::Relative => [
                    RecurrenceField::YearRelativeDayOrdinal->value => $this->resolveDayOrdinal($params[RecurrenceField::YearRelativeDayOrdinal->value]),
                    RecurrenceField::YearRelativeDay->value => $this->resolveDay($params[RecurrenceField::YearRelativeDay->value]),
                ],
            }];
    }

    private function resolveMode(RecurrenceMode|int $mode): RecurrenceMode
    {
        return $mode instanceof RecurrenceMode ? $mode : RecurrenceMode::from($mode);
    }

    private function resolveWeekOrdinal(WeekOrdinal|int $weekOrdinal): WeekOrdinal
    {
        return $weekOrdinal instanceof WeekOrdinal ? $weekOrdinal : WeekOrdinal::from($weekOrdinal);
    }

    private function resolveDayOrdinal(DayOrdinal|int $dayOrdinal): DayOrdinal
    {
        return $dayOrdinal instanceof DayOrdinal ? $dayOrdinal : DayOrdinal::from($dayOrdinal);
    }

    private function resolveMonth(Month|int $month): Month
    {
        return $month instanceof Month ? $month : Month::from($month);
    }

    /**
     * @return Day[]
     */
    private function resolveDays(array $days): array
    {
        return array_map(fn(Day|int $day): Day => $this->resolveDay($day), $days);
    }

    private function resolveDay(Day|int $day): Day
    {
        return $day instanceof Day ? $day : Day::from($day);
    }

    private function resolveTaskRecurrence(RecurrenceType|int|null $taskRecurrence): ?RecurrenceType
    {
        return match (true) {
            $taskRecurrence === null => null,
            is_int($taskRecurrence) => RecurrenceType::from($taskRecurrence),
            default => $taskRecurrence,
        };
    }

    private function resolveCategory(TaskCategory|int|null $category): ?TaskCategory
    {
        return $category === null ? null :$this->taskCategoryProvider->resolveTaskCategory($category);
    }

    private function resolveDateFromUTCDateTime(DateTimeInterface $dateTime): DateTimeInterface
    {
        $timezone = $this->getUserTimezone();

        return $this->dateTimeProvider->asDateImmutableUTC($dateTime, new DateTimeZone($timezone->getName()));
    }

    #[Required]
    public function setDependencies(
        DateTimeProvider $dateTimeProvider,
        UserProvider $userProvider,
        TaskCategoryProvider $taskCategoryProvider,
    ): void
    {
        $this->dateTimeProvider = $dateTimeProvider;
        $this->userProvider = $userProvider;
        $this->taskCategoryProvider = $taskCategoryProvider;
    }
}
