<?php

declare(strict_types=1);

namespace App\Controller;

use App\Context\UserContext;
use App\Forms\TaskDeleteForm;
use App\Forms\TaskForm;
use App\Task\Entity\Task;
use App\Task\Enum\RecurrenceField;
use App\Task\Enum\RecurrenceMode;
use App\Task\Enum\RecurrenceType;
use App\Task\Facade\TaskCreatorFacade;
use App\Task\Facade\TaskDeleterFacade;
use App\Task\Facade\TaskProviderFacade;
use App\Task\Facade\TaskUpdaterFacade;
use App\UserPreference\Provider\UserPreferenceProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    public function __construct(
        private readonly UserContext $userContext,
        private readonly UserPreferenceProvider $preferenceProvider,
    ) {
    }

    #[Route('/{task}', 'task_overview', requirements: ['task' => '.{36}'] ,  defaults: ['task' => null], methods: ['get'])]
    public function index(TaskProviderFacade $provider, Task $task = null): Response
    {
        $tasks = $provider->getTasksByCurrentUser();
        $form = $this->createTaskForm($task, match(true) {
            $task === null => $this->generateUrl('task_create'),
            $task !== null => $this->generateUrl('task_edit', ['task' => $task->getUuId()])
        });

        $deleteForms = $this->createTaskDeleteForms($tasks);

        $timezone = $this->preferenceProvider->getTimezone($this->userContext->getUser())->getTimezone();
        $recurrences = $task ? $provider->getOccurrences($task, $timezone, new \DateTime('2030-9-3 11:49:00', new \DateTimeZone('Europe/Amsterdam'))) : [];

        return $this->render('Task/overview.html.twig', [
            'task_form' => $form,
            'task_delete_forms' => $deleteForms,
            'tasks' => $tasks,
            'recurrences' => $recurrences,
        ]);
    }

    #[Route('/task/create', 'task_create', methods: ['post'])]
    public function create(Request $request, TaskCreatorFacade $creator, TaskProviderFacade $provider): Response
    {
        $form = $this->createTaskForm(null, $this->generateUrl('task_create'));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $name = $form['name']->getData();
            $description = $form['description']->getData();
            $duration = (int) $form['duration']->getData();
            $category = $form['category']->getData();
            $participants = iterator_to_array($form['participants']->getData());
            $dateTime = $form['datetime']->getData();
            $hasRecurrence = $form['has_recurrence']->getData();
            $recurrenceForm = $form['recurrence'];
            $recurrenceEndDate = $hasRecurrence ? $recurrenceForm['end_date']->getData() : null;
            $recurrenceType = $hasRecurrence ? $recurrenceForm['type']->getData() : null;
            $recurrenceParameters = $hasRecurrence ? $this->getRecurrenceFieldData($recurrenceForm) : [];

            $creator->create($name, $description, $duration, $category, $participants, $dateTime, $recurrenceEndDate, $recurrenceType, $recurrenceParameters);

            return $this->redirectToRoute('task_overview');
        }

        $tasks = $provider->getTasksByCurrentUser();
        $deleteForms = $this->createTaskDeleteForms($tasks);

        return $this->render('Task/overview.html.twig', [
            'task_form' => $form,
            'task_delete_forms' => $deleteForms,
            'tasks' => $tasks,
            'recurrences' => [],
        ]);
    }

    #[Route('/task/edit/{task}', 'task_edit', requirements: ['task' => '.{36}'], methods: ['post'])]
    public function edit(Request $request, TaskUpdaterFacade $updater, TaskProviderFacade $provider, Task $task): Response
    {
        $form = $this->createTaskForm($task, $this->generateUrl('task_edit', ['task' => $task->getUuid()]));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $name = $form['name']->getData();
            $description = $form['description']->getData();
            $duration = (int) $form['duration']->getData();
            $category = $form['category']->getData();
            $dateTime = $form['datetime']->getData();
            $participants = iterator_to_array($form['participants']->getData());
            $hasRecurrence = $form['has_recurrence']->getData();
            $recurrenceForm = $form['recurrence'];
            $recurrenceEndDate = $hasRecurrence ? $recurrenceForm['end_date']->getData() : null;
            $recurrenceType = $hasRecurrence ? $recurrenceForm['type']->getData() : null;
            $recurrenceParameters = $hasRecurrence ? $this->getRecurrenceFieldData($recurrenceForm) : [];

            $updater->update($task, $name, $description, $duration, $category, $participants, $dateTime, $recurrenceEndDate, $recurrenceType, $recurrenceParameters);

            return $this->redirectToRoute('task_overview', ['task' => $task->getUuid()]);
        }

        $tasks = $provider->getTasksByCurrentUser();
        $deleteForms = $this->createTaskDeleteForms($tasks);

        return $this->render('Task/overview.html.twig', [
            'task_form' => $form,
            'task_delete_forms' => $deleteForms,
            'tasks' => $tasks,
        ]);
    }

    #[Route('/task/delete/{task}', 'task_delete', requirements: ['task' => '.{36}'], methods: ['post'])]
    public function delete(Request $request, TaskDeleterFacade $deleter, Task $task): Response
    {
        $form = $this->createTaskDeleteForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $deleter->delete($task);
        }

        return $this->redirectToRoute('task_overview');
    }

    private function getRecurrenceFieldData(Form $form): array
    {
        $recurrenceInterval = $form['interval']->getData();
        $recurrenceType = $form['type']->getData();
        $recurrenceTypeWeek = $form['type_week']->getData();
        $recurrenceTypeMonth = $form['type_month']->getData();
        $recurrenceTypeYear = $form['type_year']->getData();

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

    private function createTaskForm(?Task $task, string $action): FormInterface
    {
        return $this->createForm(TaskForm::class, $task, [
            'action' => $action,
            'user' => $this->userContext->getUser(),
            'task' => $task,
        ]);
    }

    /**
     * @param Task[] $tasks
     * @return array<int, FormView>
     */
    private function createTaskDeleteForms(array $tasks): array
    {
        return array_map(
            fn(): FormView => $this->createTaskDeleteForm()->createView(),
            array_flip(array_map(static fn(Task $task): int => $task->getId(), $tasks))
        );
    }

    private function createTaskDeleteForm(): FormInterface
    {
        return $this->createForm(TaskDeleteForm::class);
    }
}
