<?php

declare(strict_types=1);

namespace App\Task\Recurrence;

use App\DateTimeProvider\DateTimeProvider;
use App\Locale\Entity\Timezone;
use App\Task\Entity\Task;
use App\Task\Enum\RecurrenceType;
use App\Task\Provider\TaskRecurrenceProvider;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;
use DateTimeZone;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

readonly class RecurrenceCalculator
{
    public function __construct(
        private TaskRecurrenceProvider $recurrenceProvider,
        private DateTimeProvider $dateTimeProvider,
        #[TaggedLocator('app.task.recurrence.type')] private ServiceLocator $recurrenceTypes,
    ) {
    }

    /**
     * @return DateTimeInterface[]
     */
    public function getRecurrence(Task $task, Timezone $timezoneOutput): array
    {
        $recurrence = $this->recurrenceProvider->getCurrentTaskRecurrence($task);

        if (!$recurrence)
        {
            return [];
        }

        $startsAt = $recurrence->getStartDate();
        $startDate = $startsAt instanceof CarbonInterface ? $startsAt : CarbonImmutable::createFromInterface($startsAt);
        $endsDate = $startDate->clone()->addYear();

        $recurringDates =  $this->getTypeHandler($recurrence->getRecurrenceType())
            ->getRecurringDates($startDate, $endsDate, $recurrence);

        return $this->setTimesAndTimezoneToDates($recurringDates, $task->getDateTime(), new DateTimeZone($timezoneOutput->getName()));
    }

    /**
     * @return DateTimeInterface[]
     *
     * @param CarbonInterface[] $dates
     */
    private function setTimesAndTimezoneToDates(array $dates, DateTimeInterface $timeFrom, DateTimeZone $timezone): array
    {
        $dateTimes = [];

        foreach($dates as $date)
        {
            $dateTimes[] = $this->dateTimeProvider->changeTimeZone($date->setTimeFrom($timeFrom->setTimezone($timezone)), $timezone);
        }

        return $dateTimes;
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
