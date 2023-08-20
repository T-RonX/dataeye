<?php

declare(strict_types=1);

namespace App\Forms;

use App\Task\Entity\Task;
use App\Task\Entity\TaskRecurrenceMonthAbsolute;
use App\Task\Entity\TaskRecurrenceMonthRelative;
use App\Task\Enum\Day;
use App\Task\Enum\RecurrenceMode;
use App\Task\Enum\WeekOrdinal;
use App\Task\Provider\TaskRecurrenceProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskRecurrenceMonthForm extends AbstractType
{
    public function __construct(
        private readonly TaskRecurrenceProvider $taskRecurrenceProvider,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $task = $this->getTask($options);
        $recurrence = $task ? $this->getRecurrence($task) : null;

        $builder
            ->add('mode', EnumType::class, [
                'label' => false,
                'data' => $recurrence instanceof TaskRecurrenceMonthAbsolute ? RecurrenceMode::Absolute : RecurrenceMode::Relative,
                'class' => RecurrenceMode::class,
                'multiple' => false,
                'expanded' => true,
                'choice_label' => fn($mode) => match ($mode) {
                    RecurrenceMode::Absolute => 'On',
                    RecurrenceMode::Relative => 'On the',
                }
            ])
            ->add('day_number', IntegerType::class, [
                'label' => false,
                'data' => $recurrence instanceof TaskRecurrenceMonthAbsolute ? $recurrence->getDayNumber() : 1,
                'attr' => [
                    'min' => 1,
                    'max' => 31,
                    'class' => 'absolute'
                ]
            ])
            ->add('week_ordinal', EnumType::class, [
                'label' => false,
                'data' => $recurrence instanceof TaskRecurrenceMonthRelative ? $recurrence->getWeekOrdinal() : WeekOrdinal::First,
                'class' => WeekOrdinal::class,
                'attr' => [
                    'class' => 'relative'
                ]
            ])
            ->add('day', EnumType::class, [
                'label' => false,
                'data' => $recurrence instanceof TaskRecurrenceMonthRelative ? $recurrence->getDay() : Day::Monday,
                'class' => Day::class,
                'attr' => [
                    'class' => 'relative'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'task' => null,
            'recurrence' => null,
        ]);
    }

    private function getTask(array $options): ?Task
    {
        return $options['task'] ?? null;
    }

    private function getRecurrence(Task $task): TaskRecurrenceMonthAbsolute|TaskRecurrenceMonthRelative|null
    {
        $recurrence = $this->taskRecurrenceProvider->getCurrentTaskRecurrence($task);

        return $recurrence instanceof TaskRecurrenceMonthAbsolute || $recurrence instanceof TaskRecurrenceMonthRelative ? $recurrence : null;
    }
}
