<?php

declare(strict_types=1);

namespace App\Forms;

use App\Task\Entity\Task;
use App\Task\Entity\TaskRecurrenceYearAbsolute;
use App\Task\Entity\TaskRecurrenceYearRelative;
use App\Task\Enum\Day;
use App\Task\Enum\DayOrdinal;
use App\Task\Enum\Month;
use App\Task\Enum\RecurrenceMode;
use App\Task\Provider\TaskRecurrenceProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskRecurrenceYearForm extends AbstractType
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
                'data' => $recurrence instanceof TaskRecurrenceYearAbsolute ? RecurrenceMode::Absolute : RecurrenceMode::Relative,
                'multiple' => false,
                'expanded' => true,
                'class' => RecurrenceMode::class,
                'choice_label' => fn($mode) => match ($mode) {
                    RecurrenceMode::Absolute => 'On',
                    RecurrenceMode::Relative => 'On the',
                },
            ])
            ->add('month_absolute', EnumType::class, [
                'label' => false,
                'data' => $recurrence instanceof TaskRecurrenceYearAbsolute ? $recurrence->getMonth() : Month::January,
                'class' => Month::class,
                'attr' => [
                    'class' => 'absolute'
                ]
            ])
            ->add('day_number', IntegerType::class, [
                'label' => false,
                'data' => $recurrence instanceof TaskRecurrenceYearAbsolute ? $recurrence->getDayNumber() : 1,
                'attr' => [
                    'min' => 1,
                    'max' => 31,
                    'class' => 'absolute'
                ],
            ])
            ->add('day_ordinal', EnumType::class, [
                'label' => false,
                'data' => $recurrence instanceof TaskRecurrenceYearRelative ? $recurrence->getDayOrdinal() : DayOrdinal::First,
                'class' => DayOrdinal::class,
            ])
            ->add('day', EnumType::class, [
                'label' => false,
                'data' => $recurrence instanceof TaskRecurrenceYearRelative ? $recurrence->getDay() : Day::Monday,
                'class' => Day::class,
                'attr' => [
                    'class' => 'relative'
                ]
            ])
            ->add('month_relative', EnumType::class, [
                'label' => 'of',
                'data' => $recurrence instanceof TaskRecurrenceYearRelative ? $recurrence->getMonth() : Month::January,
                'class' => Month::class,
                'attr' => [
                    'class' => 'absolute relative'
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

    private function getRecurrence(Task $task): TaskRecurrenceYearAbsolute|TaskRecurrenceYearRelative|null
    {
        $recurrence = $this->taskRecurrenceProvider->getCurrentTaskRecurrence($task);

        return $recurrence instanceof TaskRecurrenceYearAbsolute || $recurrence instanceof TaskRecurrenceYearRelative ? $recurrence : null;
    }
}
