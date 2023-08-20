<?php

declare(strict_types=1);

namespace App\Forms;

use App\Task\Contract\RecurrenceIntervalInterface;
use App\Task\Entity\Task;
use App\Task\Entity\TaskRecurrence;
use App\Task\Enum\RecurrenceType;
use App\Task\Provider\TaskRecurrenceProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskRecurrenceForm extends AbstractType
{
    public function __construct(
        private readonly TaskRecurrenceProvider $taskRecurrenceProvider,
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
                    'class' => 'subform recurrence_month'
                ]
            ])
            ->add('type_year', TaskRecurrenceYearForm::class, [
                'label' => false,
                'required' => false,
                'task' => $task,
                'recurrence' => $recurrence,
                'attr' => [
                    'class' => 'subform recurrence_year'
                ]
            ]);
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
