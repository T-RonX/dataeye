<?php

declare(strict_types=1);

namespace App\Forms;

use App\Task\Entity\Task;
use App\Task\Entity\TaskRecurrenceWeek;
use App\Task\Enum\Day;
use App\Task\Provider\TaskRecurrenceProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskRecurrenceWeekForm extends AbstractType
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
        ->add('days', EnumType::class, [
            'label' => false,
            'data' => $recurrence?->getDays(),
            'class' => Day::class,
            'multiple' => true,
            'expanded' => true,
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

    private function getRecurrence(Task $task): ?TaskRecurrenceWeek
    {
        $recurrence = $this->taskRecurrenceProvider->getCurrentTaskRecurrence($task);

        return $recurrence instanceof TaskRecurrenceWeek ? $recurrence : null;
    }
}
