<?php

declare(strict_types=1);

namespace App\Task\Recurrence;

use App\Locale\Entity\Timezone;
use App\Task\Entity\Task;
use App\Task\Enum\RecurrenceType;
use App\Task\Provider\TaskRecurrenceProvider;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

readonly class RecurrenceCalculator
{
    public function __construct(
        private TaskRecurrenceProvider $recurrenceProvider,
        #[TaggedLocator('app.task.recurrence.type')] private ServiceLocator $recurrenceTypes,
    ) {
    }

    /**
     * @return DateTimeInterface[]
     */
    public function getRecurrence(Task $task, Timezone $timezone): array
    {
        $recurrence = $this->recurrenceProvider->getCurrentTaskRecurrence($task);

        if (!$recurrence)
        {
            return [];
        }

        $startsAt = $recurrence->getStartsAt();
        $startDate = $startsAt instanceof CarbonInterface ? $startsAt : CarbonImmutable::createFromInterface($startsAt);
        $endsDate = $startsAt->clone()->addYear();

        return $this->getTypeHandler($recurrence->getRecurrenceType())
            ->getRecurringDates($startDate, $endsDate, $recurrence);
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
