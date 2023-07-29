<?php

declare(strict_types=1);

namespace App\Controller;

use App\Forms\TaskDeleteForm;
use App\Forms\TaskForm;
use App\Task\Entity\Task;
use App\Task\Provider\TaskProvider;
use App\Task\TaskHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskProvider $taskProvider,
        private readonly TaskHandler $taskHandler
    ) {
    }

    #[Route('/{task}', 'task_overview', requirements: ['task' => '\d+'] ,  defaults: ['task' => null], methods: ['get'])]
    public function index(Task $task = null): Response
    {
        $tasks = $this->taskProvider->getAllTask();
        $form = $this->createTaskForm($task, match(true) {
            $task === null => $this->generateUrl('task_create'),
            $task !== null => $this->generateUrl('task_update', ['task' => $task->getId()])
        });

        $deleteForms = $this->createTaskDeleteForms($tasks);

        return $this->render('Task/overview.html.twig', [
            'task_form' => $form,
            'task_delete_forms' => $deleteForms,
            'tasks' => $tasks,
        ]);
    }

    #[Route('/task/create', 'task_create', methods: ['post'])]
    public function create(Request $request): Response
    {
        $form = $this->createTaskForm(null, $this->generateUrl('task_create'));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $name = $form->getData()['name'];
            $this->taskHandler->create($name);

            return $this->redirectToRoute('task_overview');
        }

        $tasks = $this->taskProvider->getAllTask();
        $deleteForms = $this->createTaskDeleteForms($tasks);

        return $this->render('Task/overview.html.twig', [
            'task_form' => $form,
            'task_delete_forms' => $deleteForms,
            'tasks' => $tasks,
        ]);
    }

    #[Route('/task/update/{task}', 'task_update', requirements: ['task' => '\d+'], methods: ['post'])]
    public function update(Request $request, Task $task): Response
    {
        $form = $this->createTaskForm($task, $this->generateUrl('task_update', ['task' => $task->getId()]));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $name = $form['name']->getData();
            $this->taskHandler->update($task, $name);

            return $this->redirectToRoute('task_overview');
        }

        $tasks = $this->taskProvider->getAllTask();
        $deleteForms = $this->createTaskDeleteForms($tasks);

        return $this->render('Task/overview.html.twig', [
            'task_form' => $form,
            'task_delete_forms' => $deleteForms,
            'tasks' => $tasks,
        ]);
    }

    #[Route('/task/delete/{task}', 'task_delete', requirements: ['task' => '\d+'], methods: ['post'])]
    public function delete(Request $request, Task $task): Response
    {
        $form = $this->createTaskDeleteForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->taskHandler->delete($task);
        }

        return $this->redirectToRoute('task_overview');
    }

    private function createTaskForm(?Task $task, string $action): FormInterface
    {
        return $this->createForm(TaskForm::class, $task, [
            'action' => $action
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
