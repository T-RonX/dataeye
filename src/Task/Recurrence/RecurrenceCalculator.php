<?php

declare(strict_types=1);

namespace App\Task\Recurrence;

use App\DateTimeProvider\DateTimeProvider;
use App\Task\Entity\Task;
use App\Task\Entity\TaskDeferral;
use App\Task\Entity\TaskPostpone;
use App\Task\Entity\TaskRecurrence;
use App\Task\Entity\TaskSkip;
use App\Task\Enum\RecurrenceType;
use App\Task\Provider\TaskDeferralProvider;
use App\Task\Provider\TaskRecurrenceProvider;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

readonly class RecurrenceCalculator
{
    public function __construct(
        private TaskRecurrenceProvider $recurrenceProvider,
        private TaskDeferralProvider $deferralProvider,
        private DateTimeProvider $dateTimeProvider,
        #[TaggedLocator('app.task.recurrence.type')] private ServiceLocator $recurrenceTypes,
    ) {
    }

    /**
     * @return DateTimeInterface[]
     */
    public function getRecurrence(Task $task, DateTimeZone $timezoneOutput, int|DateTimeInterface $limit, DateTimeInterface $lowerBound = null): array
    {
        $recurrence = $this->recurrenceProvider->getCurrentTaskRecurrence($task);
        $dates = $this->getRecurringDates($task, $recurrence, $timezoneOutput, $limit, $lowerBound);

        return $this->updateLowerBoundWithDeferrals($task, $recurrence, $dates, $timezoneOutput);
    }

    private function getRecurringDates(Task $task, ?TaskRecurrence $recurrence, DateTimeZone $timezoneOutput, int|DateTimeInterface $limit, DateTimeInterface $lowerBound = null): array
    {
        $taskDateLocal = $task->getDateTime()->setTimezone($timezoneOutput);

        if (!$recurrence)
        {
            return [$taskDateLocal];
        }

        $recurrenceStartDate = $this->dateTimeProvider->changeTimeZone($recurrence->getStartDate(), $timezoneOutput);
        $startDate = ($recurrenceStartDate instanceof CarbonInterface ? $recurrenceStartDate : new CarbonImmutable($recurrenceStartDate))
            ->setTimeFrom($taskDateLocal);

        if ($lowerBound !== null)
        {
            $startDate = max($startDate, (new CarbonImmutable($lowerBound))->setTimeFrom($startDate));
        }

        $maxEndDate = ($recurrence->getEndDate() ?: (new CarbonImmutable($this->dateTimeProvider->getNow()))->addYear(1))
            ->setTimezone($timezoneOutput);
        $endDate = ($maxEndDate instanceof CarbonInterface ? $maxEndDate : new CarbonImmutable($maxEndDate))
            ->endOfDay();

        if ($limit instanceof DateTimeInterface)
        {
            $limit = min($endDate, $limit);
        }

        return $this->getTypeHandler($recurrence->getRecurrenceType())
            ->getRecurringDates($startDate, $recurrence, $limit);
    }

    /**
     * @param CarbonInterface[] $dates
     *
     * @return CarbonInterface[]
     */
    private function updateLowerBoundWithDeferrals(Task $task, ?TaskRecurrence $recurrence, array $dates, DateTimeZone $timezone): array
    {
        $currentOccurrence = $dates[0] ?? null;
        $nextOccurrence = $this->getRecurringDates($task, $recurrence, $timezone, 2, $currentOccurrence)[1] ?? null;

        if ($currentOccurrence === null)
        {
            return $dates;
        }

        $deferral = $this->getActiveDeferral($recurrence ?: $task, $timezone, $currentOccurrence, $nextOccurrence);

        if ($deferral instanceof TaskSkip)
        {
            array_shift($dates);
        }

        if ($deferral instanceof TaskPostpone)
        {
            $newDate = $deferral->getDelayedTo()->setTimezone($timezone);
            $dates[0] = $newDate instanceof DateTime ? new CarbonImmutable($newDate, $timezone) : $newDate;
        }

        return $dates;
    }

    /**
     * @return TaskDeferral|null
     */
    private function getActiveDeferral(TaskRecurrence|Task $source, DateTimeZone $timezone, DateTimeInterface $currentOccurrence, ?DateTimeInterface $nextOccurrence): ?TaskDeferral
    {
        $latestPostpone = null;
        $latestSkip = null;

        foreach ($this->getCurrentDeferrals($source, $timezone, $currentOccurrence, $nextOccurrence) as $deferral)
        {
            if ($deferral instanceof TaskSkip && $deferral->getDeferredAt() > $latestSkip?->getDeferredAt())
            {
                $latestSkip = $deferral;
            }

            if ($deferral instanceof TaskPostpone && $deferral->getDelayedTo() > $latestPostpone?->getDelayedTo())
            {
                $latestPostpone = $deferral;
            }
        }

        return  $latestSkip ?: $latestPostpone;
    }

    /**
     * @return TaskDeferral[]
     */
    private function getCurrentDeferrals(TaskRecurrence|Task $source, DateTimeZone $timezone, DateTimeInterface $currentOccurrence, ?DateTimeInterface $nextOccurrence): array
    {
        return array_filter(
            $this->deferralProvider->getByTaskOrRecurrence($source),
            static function(TaskDeferral $deferral) use ($timezone, $currentOccurrence, $nextOccurrence): bool {
                $date = (match (true) {
                        $deferral instanceof TaskPostpone => $deferral->getDelayedTo(),
                        $deferral instanceof TaskSkip => $deferral->getDeferredAt(), // Assuming we are in the current task window. Can not skip task in advance.
                    })->setTimezone($timezone);

                $isWithingLowerBound = $date >= $currentOccurrence; // When TaskSkip, check for Completed instances in previous occurrence to make Skip work in advance
                $isWithingUpperBound = !$nextOccurrence || $date < $nextOccurrence;

                return $isWithingLowerBound && $isWithingUpperBound;
            }
        );
    }

    private function getTypeHandler(RecurrenceType $type): TypeHandlerInterface
    {
        if (!$this->recurrenceTypes->has($type->name))
        {
            throw new RuntimeException("Recurrence type '{$type->name}' is not implemented.");
        }

        return $this->recurrenceTypes->get($type->name);
    }
}
