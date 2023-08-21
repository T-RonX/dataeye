<?php

declare(strict_types=1);

namespace App\Forms;

use App\DateTimeProvider\DateTimeProvider;
use App\Forms\Exception\MissingFormOptionException;
use App\Task\Entity\Task;
use App\Task\Entity\TaskCategory;
use App\Task\Provider\TaskCategoryProvider;
use App\Task\Provider\TaskParticipantProvider;
use App\Task\Provider\TaskRecurrenceProvider;
use App\User\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskForm extends AbstractType
{
    public function __construct(
        private readonly TaskCategoryProvider $taskCategoryProvider,
        private readonly TaskRecurrenceProvider $taskRecurrenceProvider,
        private readonly TaskParticipantProvider $taskParticipantProvider,
        private readonly DateTimeProvider $dateTimeProvider,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $task = $this->getTask($options);
        $user = $this->getUser($options);

        $recurrence = $task ? $this->taskRecurrenceProvider->getCurrentTaskRecurrence($task) : null;
        $participants = $task ? $this->taskParticipantProvider->getTaskParticipantUsers($task) : null;

        $builder
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('duration', NumberType::class)
            ->add('category', EntityType::class, [
                'class' => TaskCategory::class,
                'choice_label' => 'name',
                'choices' => $this->taskCategoryProvider->getByOwner($user),
                'required' => false,
            ])
            ->add('starts_at', DateTimeType::class, [
                'mapped' => false,
                'widget' => 'single_text',
                'data' => $recurrence?->getStartsAt() ?: $this->dateTimeProvider->getNow(),
                'required' => false,
            ])
            ->add('has_recurrence', CheckboxType::class, [
                'label' => 'Repeat every...',
                'data' => $recurrence !== null,
                'required' => false,
                'mapped' => false,
            ])
            ->add('recurrence', TaskRecurrenceForm::class, [
                'mapped' => false,
                'label' => false,
                'task' => $task,
                'recurrence' => $recurrence,
            ])
            ->add('ends_at', DateType::class, [
                'mapped' => false,
                'widget' => 'single_text',
                'data' => $recurrence?->getEndsAt(),
                'required' => false,
            ])
            ->add('participants', EntityType::class, [
                'class' => User::class,
                'mapped' => false,
                'required' => false,
                'choice_label' => 'username',
                'choices' => $user->getAssociatedWith()->toArray(),
                'data' => $participants,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
            ])
            ->add('cancel', ButtonType::class, [
                'label' => 'Cancel',
                'attr' => ['class' => 'cancel']
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
        return $options['user'] ?? throw new MissingFormOptionException('user', self::class);
    }

    private function getTask(array $options): ?Task
    {
        return $options['task'] ?? null;
    }
}
