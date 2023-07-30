<?php

declare(strict_types=1);

namespace App\Controller;

use App\Forms\LoginForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/user/login', 'user_login', methods: ['get', 'post'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED'))
        {
            return $this->redirect($this->generateUrl('task_overview'));
        }

        $error = $authenticationUtils->getLastAuthenticationError();

        $form = $this->createForm(LoginForm::class);
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('Login/login.html.twig', [
            'login_form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/user/logout', 'user_logout', methods: ['get'])]
    public function logout(Security $security): Response
    {
        $response = $security->logout(false);

        if ($response !== null)
        {
            return $response;
        }

        return $this->redirectToRoute('task_overview');
    }
}