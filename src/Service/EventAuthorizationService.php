<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;

readonly class EventAuthorizationService
{
    /**
     * Vérifie si un utilisateur est le propriétaire d'un événement.
     */
    public function isOwner(User $user, Event $event): bool
    {
        return $event->getOwner()->getId() === $user->getId();
    }

    /**
     * Vérifie que l'utilisateur est le propriétaire, sinon lève une exception.
     *
     * @throws \LogicException
     */
    public function ensureIsOwner(User $user, Event $event): void
    {
        if (!$this->isOwner($user, $event)) {
            throw new \LogicException('Vous ne pouvez effectuer cette action que sur vos propres événements.');
        }
    }

    /**
     * Vérifie si un utilisateur peut s'inscrire à un événement.
     */
    public function canSubscribe(User $user, Event $event): bool
    {
        if ($this->isOwner($user, $event)) {
            return false;
        }

        if ($event->getParticipants()->contains($user)) {
            return false;
        }

        return true;
    }
}
