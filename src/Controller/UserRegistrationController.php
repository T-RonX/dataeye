<?php

declare(strict_types=1);

namespace App\Controller;

use App\Forms\UserRegistrationForm;
use App\User\Creator\UserCreator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserRegistrationController extends AbstractController
{
    public function __construct(
        private readonly UserCreator $userCreator,
        private readonly Security $security,
    ) {
    }

    #[Route('/user/new', 'user_new', methods: ['get'])]
    public function new(Request $request): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED'))
        {
            return $this->redirect($this->generateUrl('task_overview'));
        }

        $form = $this->createForm(UserRegistrationForm::class, options: ['action' => $this->generateUrl('user_register')]);
        $form->handleRequest($request);

        return $this->render('User/register.html.twig', [
            'registration_form' => $form,
        ]);
    }

    #[Route('/user/register', 'user_register', methods: ['post'])]
    public function register(Request $request): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED'))
        {
            return $this->redirect($this->generateUrl('task_overview'));
        }

        $form = $this->createForm(UserRegistrationForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $username = $form['username']->getData();
            $password = $form['password']->getData();

            $this->userCreator->create($username, $password);

            return $this->redirectToRoute('task_overview');
        }

        return $this->render('User/register.html.twig', [
            'registration_form' => $form,
        ]);
    }
}