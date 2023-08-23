<?php

declare(strict_types=1);

namespace App\Controller;

use App\Context\UserContext;
use App\Forms\UserPreferencesForm;
use App\UserPreference\Persistor\UserPreferencePersistor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserPreferencesController extends AbstractController
{
    public function __construct(
        private readonly UserContext $userContext,
        private readonly UserPreferencePersistor $preferencePersistor,
    ) {
    }

    #[Route('/user/preferences', 'user_preferences', methods: ['get'])]
    public function show(): Response
    {
        $form = $this->createForm(UserPreferencesForm::class, null, [
            'action' => $this->generateUrl('user_preferences_save'),
            'user' => $this->userContext->getUser(),
        ]);

        return $this->render('User/preferences.html.twig', [
            'preferences_form' => $form->createView(),
        ]);
    }

    #[Route('/user/preferences/save', 'user_preferences_save', methods: ['post'])]
    public function save(Request $request): Response
    {
        $form = $this->createForm(UserPreferencesForm::class, null, [
            'user' => $this->userContext->getUser(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $preferences = [
                'timezone' => $form['timezone']->getData(),
            ];

            $this->preferencePersistor->save($this->userContext->getUser(), $preferences);

            return $this->redirect($this->generateUrl('user_preferences'));
        }

        return $this->render('User/preferences.html.twig', [
            'preferences_form' => $form->createView(),
        ]);
    }
}