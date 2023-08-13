<?php

declare(strict_types=1);

namespace App\Forms;

use App\DateTimeProvider\DateTimeProvider;
use App\Forms\Exception\MissingFormOptionException;
use App\Task\Entity\Task;
use App\Task\Enum\Day;
use App\Task\Enum\DayOrdinal;
use App\Task\Enum\Month;
use App\Task\Enum\TaskRecurrenceMode;
use App\Task\Provider\TaskRecurrenceProvider;
use App\User\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskRecurrenceYearForm extends AbstractType
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
                    'On' => TaskRecurrenceMode::Absolute->value,
                    'On the' => TaskRecurrenceMode::Relative->value,
                ],
            ])
            ->add('month_absolute', ChoiceType::class, [
                'label' => false,
                'data' => Month::January->value,
                'choices' => [
                    Month::January->name => Month::January->value,
                    Month::February->name => Month::February->value,
                    Month::March->name => Month::March->value,
                    Month::April->name => Month::April->value,
                    Month::May->name => Month::May->value,
                    Month::June->name => Month::June->value,
                    Month::July->name => Month::July->value,
                    Month::August->name => Month::August->value,
                    Month::September->name => Month::September->value,
                    Month::October->name => Month::October->value,
                    Month::November->name => Month::November->value,
                    Month::December->name => Month::December->value,
                ],
                'attr' => [
                    'class' => 'absolute'
                ]
            ])
            ->add('day_number', IntegerType::class, [
                'label' => false,
                'data' => 1,
                'attr' => [
                    'min' => 1,
                    'max' => 31,
                    'class' => 'relative'
                ],
            ])
            ->add('day_ordinal', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    DayOrdinal::First->name => DayOrdinal::First->value,
                    DayOrdinal::Second->name => DayOrdinal::Second->value,
                    DayOrdinal::Third->name => DayOrdinal::Third->value,
                    DayOrdinal::Fourth->name => DayOrdinal::Fourth->value,
                    DayOrdinal::Last->name => DayOrdinal::Last->value,
                ],
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
            ])
            ->add('month_relative', ChoiceType::class, [
                'label' => 'of',
                'data' => Month::January->value,
                'choices' => [
                    Month::January->name => Month::January->value,
                    Month::February->name => Month::February->value,
                    Month::March->name => Month::March->value,
                    Month::April->name => Month::April->value,
                    Month::May->name => Month::May->value,
                    Month::June->name => Month::June->value,
                    Month::July->name => Month::July->value,
                    Month::August->name => Month::August->value,
                    Month::September->name => Month::September->value,
                    Month::October->name => Month::October->value,
                    Month::November->name => Month::November->value,
                    Month::December->name => Month::December->value,
                ],
                'attr' => [
                    'class' => 'absolute relative'
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
