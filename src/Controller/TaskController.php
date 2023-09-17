<?php

declare(strict_types=1);

namespace App\Controller;

use App\Context\UserContext;
use App\Facades\Task\Result\TaskOccurrences;
use App\Facades\Task\TaskCompleterFacade;
use App\Facades\Task\TaskCreatorFacade;
use App\Facades\Task\TaskDeleterFacade;
use App\Facades\Task\TaskPostponerFacade;
use App\Facades\Task\TaskProviderFacade;
use App\Facades\Task\TaskUpdaterFacade;
use App\Forms\TaskCompleteForm;
use App\Forms\TaskDeleteForm;
use App\Forms\TaskForm;
use App\Forms\TaskPostponeForm;
use App\Task\Entity\Task;
use App\Task\Enum\PostponeMethod;
use App\UserPreference\Provider\UserPreferenceProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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


//        $timezone = $this->preferenceProvider->getTimezone($this->userContext->getUser())->getTimezone();
        //$occurrences = $task ? $provider->getOccurrences($task, $timezone, 100, new \DateTime()) : null;
        $nextOccurrences = $provider->getNextOccurrencesForCurrentUser();

        $deleteForms = $this->createTaskDeleteForms($tasks);
        $postponeForms = $this->createTaskPostponeForms($nextOccurrences);
        $completeForms = $this->createTaskCompleteForms($nextOccurrences);

        return $this->render('Task/overview.html.twig', [
            'task_form' => $form->createView(),
            'task_delete_forms' => $deleteForms,
            'task_postpone_forms' => $postponeForms,
            'task_complete_forms' => $completeForms,
            'tasks' => $tasks,
            'occurrences' => [], //$occurrences,
            'nextOccurrences' => $nextOccurrences,
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
            $dateTime = $form['datetime']->getData();
            $participants = iterator_to_array($form['participants']->getData());
            $hasRecurrence = $form['has_recurrence']->getData();
            $recurrenceEndDate = $hasRecurrence ? $form['recurrence']['end_date']->getData() : null;
            $recurrenceType = $hasRecurrence ? $form['recurrence']['type']->getData() : null;
            $recurrenceParameters = $hasRecurrence ? $form['recurrence']->getData() : [];

            $creator->create($name, $description, $duration, $category, $participants, $dateTime, $recurrenceEndDate, $recurrenceType, $recurrenceParameters);

            return $this->redirectToRoute('task_overview');
        }

        $tasks = $provider->getTasksByCurrentUser();
        $deleteForms = $this->createTaskDeleteForms($tasks);

        return $this->render('Task/overview.html.twig', [
            'task_form' => $form->createView(),
            'task_delete_forms' => $deleteForms,
            'tasks' => $tasks,
            'occurrences' => null,
            'nextOccurrences' => [],
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
            $recurrenceEndDate = $hasRecurrence ? $form['recurrence']['end_date']->getData() : null;
            $recurrenceType = $hasRecurrence ? $form['recurrence']['type']->getData() : null;
            $recurrenceParameters = $hasRecurrence ? $form['recurrence']->getData() : [];

            $updater->update($task, $name, $description, $duration, $category, $participants, $dateTime, $recurrenceEndDate, $recurrenceType, $recurrenceParameters);

            return $this->redirectToRoute('task_overview', ['task' => $task->getUuid()]);
        }

        $tasks = $provider->getTasksByCurrentUser();
        $deleteForms = $this->createTaskDeleteForms($tasks);

        return $this->render('Task/overview.html.twig', [
            'task_form' => $form->createView(),
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

    #[Route('/task/postpone/{task}', 'task_postpone', requirements: ['task' => '.{36}'], methods: ['post'])]
    public function postpone(Request $request, TaskPostponerFacade $postponer, Task $task): Response
    {
        $form = $this->createTaskPostponeForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $delay = $form['delay']->getData();

            $postponer->postpone($task, $delay ? PostponeMethod::TimeDelay : PostponeMethod::SkipOnce, $delay);
        }

        return $this->redirectToRoute('task_overview');
    }

    #[Route('/task/complete/{task}', 'task_complete', requirements: ['task' => '.{36}'], methods: ['post'])]
    public function complete(Request $request, TaskCompleterFacade $completer, Task $task): Response
    {
        $form = $this->createTaskCompleteForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $completer->complete($task);
        }

        return $this->redirectToRoute('task_overview');
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

    /**
     * @param TaskOccurrences[] $occurrences
     * @return array<int, FormView>
     */
    private function createTaskPostponeForms(array $occurrences): array
    {
        return array_map(
            fn(): FormView => $this->createTaskPostponeForm()->createView(),
            array_flip(array_map(static fn(TaskOccurrences $occurrence): int => $occurrence->getTask()->getId(), $occurrences))
        );
    }

    /**
     * @param TaskOccurrences[] $occurrences
     * @return array<int, FormView>
     */
    private function createTaskCompleteForms(array $occurrences): array
    {
        return array_map(
            fn(): FormView => $this->createTaskCompleteForm()->createView(),
            array_flip(array_map(static fn(TaskOccurrences $occurrence): int => $occurrence->getTask()->getId(), $occurrences))
        );
    }

    private function createTaskDeleteForm(): FormInterface
    {
        return $this->createForm(TaskDeleteForm::class);
    }

    private function createTaskPostponeForm(): FormInterface
    {
        return $this->createForm(TaskPostponeForm::class);
    }

    private function createTaskCompleteForm(): FormInterface
    {
        return $this->createForm(TaskCompleteForm::class);
    }
}
