<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\EventType;
use App\Service\EventAuthorizationService;
use App\Service\EventManagementService;
use App\Service\EventSubscriptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class EventController extends AbstractController
{
    public function __construct(
        private readonly EventManagementService $eventManagementService,
        private readonly EventSubscriptionService $eventSubscriptionService,
        private readonly EventAuthorizationService $eventAuthorizationService,
    ) {
    }

    #[Route('/my-events', name: 'app_my_events')]
    #[IsGranted('ROLE_USER')]
    public function myEvents(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw new \LogicException('User must be logged in to view their events.');
        }

        $events = $user->getEvents();

        return $this->render('event/my-events.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/event/{id}', name: 'app_event_show', requirements: ['id' => '\d+'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/event/new', name: 'app_event_new')]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw new \LogicException('User must be logged in to create an event.');
        }

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventManagementService->createEvent($event, $user);

            $this->addFlash('success', 'Événement créé avec succès.');

            return $this->redirectToRoute('app_index');
        }

        return $this->render('event/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/event/{id}/edit', name: 'app_event_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Event $event): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw new \LogicException('User must be logged in to edit an event.');
        }

        // Vérifier les permissions avant de créer le formulaire
        if (!$this->eventAuthorizationService->isOwner($user, $event)) {
            $this->addFlash('error', 'Vous ne pouvez modifier que vos propres événements.');

            return $this->redirectToRoute('app_index');
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->eventManagementService->updateEvent($event, $user);
                $this->addFlash('success', 'Événement modifié avec succès.');

                return $this->redirectToRoute('app_index');
            } catch (\LogicException $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->redirectToRoute('app_index');
            }
        }

        return $this->render('event/edit.html.twig', [
            'form' => $form,
            'event' => $event,
        ]);
    }

    #[Route('/event/{id}/delete', name: 'app_event_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Event $event): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw new \LogicException('User must be logged in to delete an event.');
        }

        $token = $request->request->get('_token');
        if (!is_string($token)) {
            $this->addFlash('error', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('app_index');
        }

        if (!$this->isCsrfTokenValid('delete'.$event->getId(), $token)) {
            $this->addFlash('error', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('app_index');
        }

        try {
            $this->eventManagementService->deleteEvent($event, $user);
            $this->addFlash('success', 'Événement supprimé avec succès.');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_index');
    }

    #[Route('/event/{id}/subscribe', name: 'app_event_subscribe', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function subscribe(Request $request, Event $event): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw new \LogicException('User must be logged in to subscribe to an event.');
        }

        $token = $request->request->get('_token');

        if (!is_string($token) || !$this->isCsrfTokenValid('subscribe'.$event->getId(), $token)) {
            $this->addFlash('error', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        try {
            $this->eventSubscriptionService->subscribe($user, $event);
            $this->addFlash('success', 'Vous êtes maintenant inscrit à cet événement.');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }

    #[Route('/event/{id}/unsubscribe', name: 'app_event_unsubscribe', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function unsubscribe(Request $request, Event $event): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw new \LogicException('User must be logged in to unsubscribe from an event.');
        }

        $token = $request->request->get('_token');

        if (!is_string($token) || !$this->isCsrfTokenValid('unsubscribe'.$event->getId(), $token)) {
            $this->addFlash('error', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        try {
            $this->eventSubscriptionService->unsubscribe($user, $event);
            $this->addFlash('success', 'Vous êtes maintenant désinscrit de cet événement.');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }
}
