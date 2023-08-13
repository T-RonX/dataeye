<?php

declare(strict_types=1);

namespace App\Forms;

use App\DateTimeProvider\DateTimeProvider;
use App\Forms\Enum\TaskRecurrence;
use App\Forms\Exception\MissingFormOptionException;
use App\Task\Entity\Task;
use App\Task\Provider\TaskRecurrenceProvider;
use App\User\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskRecurrenceForm extends AbstractType
{
    public function __construct(
        private readonly TaskRecurrenceProvider $taskRecurrenceProvider,
        private readonly DateTimeProvider $dateTimeProvider,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $task = $this->getTask($options);
        $user = $this->getUser($options);

        $recurrence = $task ? $this->taskRecurrenceProvider->getCurrentTaskRecurrence($task) : null;

        $builder
            ->add('interval', IntegerType::class, [
                'label' => false,
                'data' => 1,
                'attr' => [
                    'min' => 1,
                    'max' => 100,
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    TaskRecurrence::Day->name => TaskRecurrence::Day->value,
                    TaskRecurrence::Week->name => TaskRecurrence::Week->value,
                    TaskRecurrence::Month->name => TaskRecurrence::Month->value,
                    TaskRecurrence::Year->name => TaskRecurrence::Year->value,
                ]
            ])
            ->add('type_week', TaskRecurrenceWeekForm::class, [
                'label' => false,
                'required' => false,
                'task' => $task,
                'user' => $user,
                'attr' => [
                    'class' => 'subform recurrence_week',
                ]
            ])
            ->add('type_month', TaskRecurrenceMonthForm::class, [
                'label' => false,
                'required' => false,
                'task' => $task,
                'user' => $user,
                'attr' => [
                    'class' => 'subform recurrence_month'
                ]
            ])
            ->add('type_year', TaskRecurrenceYearForm::class, [
                'label' => false,
                'required' => false,
                'task' => $task,
                'user' => $user,
                'attr' => [
                    'class' => 'subform recurrence_year'
                ]
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user' => null,
            'task' => null,
        ]);
    }

    private function getUser(array $options): User
    {
        return $options['user'] ?? throw new MissingFormOptionException('user', $this::class);
    }

    private function getTask(array $options): ?Task
    {
        return $options['task'] ?? null;
    }
}
