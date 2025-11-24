<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;

readonly class EventSubscriptionService
{
    public function __construct(
        private EventRepository $eventRepository,
        private EventAuthorizationService $authorizationService,
    ) {
    }

    /**
     * Inscrit un utilisateur à un événement.
     *
     * @throws \LogicException Si l'utilisateur est déjà inscrit
     */
    public function subscribe(User $user, Event $event): void
    {
        if (!$this->authorizationService->canSubscribe($user, $event)) {
            if ($this->authorizationService->isOwner($user, $event)) {
                throw new \LogicException('Vous ne pouvez pas vous inscrire à votre propre événement.');
            }

            throw new \LogicException('Vous êtes déjà inscrit à cet événement.');
        }

        $event->addParticipant($user);
        $this->eventRepository->flush();
    }

    /**
     * Désinscrit un utilisateur d'un événement.
     *
     * @throws \LogicException Si l'utilisateur n'est pas inscrit
     */
    public function unsubscribe(User $user, Event $event): void
    {
        if (!$event->getParticipants()->contains($user)) {
            throw new \LogicException('Vous n\'êtes pas inscrit à cet événement.');
        }

        $event->removeParticipant($user);
        $this->eventRepository->flush();
    }
}
