<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;

readonly class EventManagementService
{
    public function __construct(
        private EventRepository $eventRepository,
        private EventAuthorizationService $authorizationService,
    ) {
    }

    /**
     * Crée un nouvel événement.
     */
    public function createEvent(Event $event, User $owner): void
    {
        $event->setOwner($owner);
        $this->eventRepository->save($event);
    }

    /**
     * Modifie un événement existant.
     *
     * @throws \LogicException Si l'utilisateur n'est pas le propriétaire
     */
    public function updateEvent(Event $event, User $user): void
    {
        $this->authorizationService->ensureIsOwner($user, $event);
        $this->eventRepository->flush();
    }

    /**
     * Supprime un événement.
     *
     * @throws \LogicException Si l'utilisateur n'est pas le propriétaire
     */
    public function deleteEvent(Event $event, User $user): void
    {
        $this->authorizationService->ensureIsOwner($user, $event);
        $this->eventRepository->remove($event);
    }
}
