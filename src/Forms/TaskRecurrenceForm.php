<?php

declare(strict_types=1);

namespace App\Forms;

use App\Context\UserContext;
use App\Forms\Transformer\CallbackReverseTransformer;
use App\Task\Contract\RecurrenceIntervalInterface;
use App\Task\Entity\Task;
use App\Task\Entity\TaskRecurrence;
use App\Task\Enum\RecurrenceField;
use App\Task\Enum\RecurrenceMode;
use App\Task\Enum\RecurrenceType;
use App\Task\Provider\TaskRecurrenceProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskRecurrenceForm extends AbstractType
{
    public function __construct(
        private readonly TaskRecurrenceProvider $taskRecurrenceProvider,
        private readonly UserContext $userContext,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $task = $this->getTask($options);
        $recurrence = $this->getRecurrence($task);

        $builder
            ->add('interval', IntegerType::class, [
                'label' => false,
                'data' => $recurrence instanceof RecurrenceIntervalInterface ? $recurrence->getInterval() : 1,
                'attr' => [
                    'min' => 1,
                    'max' => 100,
                ]
            ])
            ->add('type', EnumType::class, [
                'label' => false,
                'class' => RecurrenceType::class,
                'data' => $recurrence?->getRecurrenceType(),
            ])
            ->add('type_week', TaskRecurrenceWeekForm::class, [
                'label' => false,
                'required' => false,
                'task' => $task,
                'recurrence' => $recurrence,
                'attr' => [
                    'class' => 'subform recurrence_week',
                ]
            ])
            ->add('type_month', TaskRecurrenceMonthForm::class, [
                'label' => false,
                'required' => false,
                'task' => $task,
                'recurrence' => $recurrence,
                'attr' => [
                    'class' => 'subform recurrence_month',
                ]
            ])
            ->add('type_year', TaskRecurrenceYearForm::class, [
                'label' => false,
                'required' => false,
                'task' => $task,
                'recurrence' => $recurrence,
                'attr' => [
                    'class' => 'subform recurrence_year',
                ]
            ])
            ->add('end_date', DateType::class, [
                'input' => 'datetime_immutable',
                'mapped' => false,
                'widget' => 'single_text',
                'data' => $recurrence?->getEndDate(),
                'required' => false,
            ]);

        $builder->addModelTransformer(new CallbackReverseTransformer(
            $this->getFieldData(...)
        ));
    }

    private function getFieldData(array $data): array
    {
        $recurrenceInterval = $data['interval'];
        $recurrenceType = $data['type'];
        $recurrenceTypeWeek = $data['type_week'];
        $recurrenceTypeMonth = $data['type_month'];
        $recurrenceTypeYear = $data['type_year'];

        $fields = [];

        switch ($recurrenceType)
        {
            case RecurrenceType::Day:
                $fields = [
                    RecurrenceField::DayInterval->value => $recurrenceInterval,
                ];
                break;

            case RecurrenceType::Week:
                $fields = [
                    RecurrenceField::WeekInterval->value => $recurrenceInterval,
                    RecurrenceField::WeekDays->value => $recurrenceTypeWeek['days'],
                ];
                break;

            case RecurrenceType::Month:
                switch ($recurrenceTypeMonth['mode'])
                {
                    case RecurrenceMode::Absolute:
                        $fields = [
                            RecurrenceField::MonthMode->value => $recurrenceTypeMonth['mode'],
                            RecurrenceField::MonthInterval->value => $recurrenceInterval,
                            RecurrenceField::MonthAbsoluteDayNumber->value => $recurrenceTypeMonth['day_number'],
                        ];
                        break;

                    case RecurrenceMode::Relative:
                        $fields = [
                            RecurrenceField::MonthMode->value => $recurrenceTypeMonth['mode'],
                            RecurrenceField::MonthInterval->value => $recurrenceInterval,
                            RecurrenceField::MonthRelativeWeekOrdinal->value => $recurrenceTypeMonth['week_ordinal'],
                            RecurrenceField::MonthRelativeDay->value => $recurrenceTypeMonth['day'],
                        ];
                        break;
                }

                break;

            case RecurrenceType::Year:
                switch ($recurrenceTypeYear['mode'])
                {
                    case RecurrenceMode::Absolute:
                        $fields = [
                            RecurrenceField::YearMode->value => $recurrenceTypeYear['mode'],
                            RecurrenceField::YearMonth->value => $recurrenceTypeYear['month_absolute'],
                            RecurrenceField::YearAbsoluteDayNumber->value => $recurrenceTypeYear['day_number'],
                        ];
                        break;

                    case RecurrenceMode::Relative:
                        $fields = [
                            RecurrenceField::YearMode->value => $recurrenceTypeYear['mode'],
                            RecurrenceField::YearRelativeDayOrdinal->value => $recurrenceTypeYear['day_ordinal'],
                            RecurrenceField::YearRelativeDay->value => $recurrenceTypeYear['day'],
                            RecurrenceField::YearMonth->value => $recurrenceTypeYear['month_relative'],
                        ];
                        break;
                }
                break;
        }

        return $fields;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'recurrence' => null,
            'task' => null,
        ]);
    }

    private function getTask(array $options): ?Task
    {
        return $options['task'] ?? null;
    }

    private function getRecurrence(?Task $task): ?TaskRecurrence
    {
        return $task ? $this->taskRecurrenceProvider->getCurrentTaskRecurrence($task) : null;
    }
}
