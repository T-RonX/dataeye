<?php

declare(strict_types=1);

namespace App\Forms;

use App\DateTimeProvider\DateTimeProvider;
use App\Forms\Exception\MissingFormOptionException;
use App\Task\Entity\Task;
use App\Task\Enum\Day;
use App\Task\Enum\TaskRecurrenceMode;
use App\Task\Enum\WeekOrdinal;
use App\Task\Provider\TaskRecurrenceProvider;
use App\User\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskRecurrenceMonthForm extends AbstractType
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
            ->add('mode', ChoiceType::class, [
                'multiple' => false,
                'expanded' => true,
                'label' => false,
                'choices' => [
                    'On day' => TaskRecurrenceMode::Absolute->value,
                    'On the' => TaskRecurrenceMode::Relative->value,
                ],
            ])
            ->add('day_number', IntegerType::class, [
                'label' => false,
                'data' => 1,
                'attr' => [
                    'min' => 1,
                    'max' => 31,
                    'class' => 'absolute'
                ]
            ])
            ->add('week_ordinal', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    WeekOrdinal::First->name => WeekOrdinal::First->value,
                    WeekOrdinal::Second->name => WeekOrdinal::Second->value,
                    WeekOrdinal::Third->name => WeekOrdinal::Third->value,
                    WeekOrdinal::Fourth->name => WeekOrdinal::Fourth->value,
                    WeekOrdinal::Last->name => WeekOrdinal::Last->value,
                ],
                'attr' => [
                    'class' => 'relative'
                ]
            ])
            ->add('day', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    Day::Monday->name => Day::Monday->value,
                    Day::Tuesday->name => Day::Tuesday->value,
                    Day::Wednesday->name => Day::Wednesday->value,
                    Day::Tuesday->name => Day::Tuesday->value,
                    Day::Friday->name => Day::Friday->value,
                    Day::Saturday->name => Day::Saturday->value,
                    Day::Sunday->name => Day::Sunday->value,
                ],
                'attr' => [
                    'class' => 'relative'
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
