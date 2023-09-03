<?php

declare(strict_types=1);

namespace App\Task\Recurrence;

use App\DateTimeProvider\DateTimeProvider;
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
    public function getRecurrence(Task $task, DateTimeZone $timezoneOutput, int|DateTimeInterface $limit): array
    {
        $recurrence = $this->recurrenceProvider->getCurrentTaskRecurrence($task);
        $taskDateLocal = $task->getDateTime()->setTimezone($timezoneOutput);

        if (!$recurrence)
        {
            return [$taskDateLocal];
        }

        $recurrenceStartDate = $this->dateTimeProvider->changeTimeZone($recurrence->getStartDate(), $timezoneOutput);
        $startDate = ($recurrenceStartDate instanceof CarbonInterface ? $recurrenceStartDate : new CarbonImmutable($recurrenceStartDate))
            ->setTimeFrom($taskDateLocal);

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

    private function getTypeHandler(RecurrenceType $type): TypeHandlerInterface
    {
        if (!$this->recurrenceTypes->has($type->name))
        {
            throw new RuntimeException("Recurrence type '{$type->name}' is not implemented.");
        }

        return $this->recurrenceTypes->get($type->name);
    }
}
