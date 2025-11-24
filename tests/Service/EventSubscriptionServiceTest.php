<?php

namespace App\Tests\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Service\EventAuthorizationService;
use App\Service\EventSubscriptionService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventSubscriptionServiceTest extends TestCase
{
    private EventRepository&MockObject $eventRepository;
    private EventAuthorizationService&MockObject $authorizationService;
    private EventSubscriptionService $service;

    protected function setUp(): void
    {
        $this->eventRepository = $this->createMock(EventRepository::class);
        $this->authorizationService = $this->createMock(EventAuthorizationService::class);
        $this->service = new EventSubscriptionService(
            $this->eventRepository,
            $this->authorizationService
        );
    }

    private function createUser(int $id): User
    {
        $user = new User();
        $user->setId($id);

        return $user;
    }

    private function createEvent(User $owner): Event
    {
        $event = new Event();
        $event->setOwner($owner);

        return $event;
    }

    public function testSubscribe(): void
    {
        $owner = $this->createUser(1);
        $user = $this->createUser(2);
        $event = $this->createEvent($owner);

        $this->authorizationService
            ->expects($this->once())
            ->method('canSubscribe')
            ->with($user, $event)
            ->willReturn(true);

        $this->eventRepository
            ->expects($this->once())
            ->method('flush');

        $this->service->subscribe($user, $event);

        $this->assertTrue($event->getParticipants()->contains($user));
    }

    public function testSubscribeThrowsExceptionWhenUserIsOwner(): void
    {
        $user = $this->createUser(1);
        $event = $this->createEvent($user);

        $this->authorizationService
            ->expects($this->once())
            ->method('canSubscribe')
            ->with($user, $event)
            ->willReturn(false);

        $this->authorizationService
            ->expects($this->once())
            ->method('isOwner')
            ->with($user, $event)
            ->willReturn(true);

        $this->eventRepository
            ->expects($this->never())
            ->method('flush');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Vous ne pouvez pas vous inscrire à votre propre événement.');

        $this->service->subscribe($user, $event);
    }

    public function testSubscribeThrowsExceptionWhenUserIsAlreadyParticipant(): void
    {
        $owner = $this->createUser(1);
        $user = $this->createUser(2);
        $event = $this->createEvent($owner);

        $this->authorizationService
            ->expects($this->once())
            ->method('canSubscribe')
            ->with($user, $event)
            ->willReturn(false);

        $this->authorizationService
            ->expects($this->once())
            ->method('isOwner')
            ->with($user, $event)
            ->willReturn(false);

        $this->eventRepository
            ->expects($this->never())
            ->method('flush');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Vous êtes déjà inscrit à cet événement.');

        $this->service->subscribe($user, $event);
    }

    public function testUnsubscribe(): void
    {
        $owner = $this->createUser(1);
        $user = $this->createUser(2);
        $event = $this->createEvent($owner);
        $event->addParticipant($user);

        $this->eventRepository
            ->expects($this->once())
            ->method('flush');

        $this->service->unsubscribe($user, $event);

        $this->assertFalse($event->getParticipants()->contains($user));
    }

    public function testUnsubscribeThrowsException(): void
    {
        $owner = $this->createUser(1);
        $user = $this->createUser(2);
        $event = $this->createEvent($owner);

        $this->eventRepository
            ->expects($this->never())
            ->method('flush');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Vous n\'êtes pas inscrit à cet événement.');

        $this->service->unsubscribe($user, $event);
    }
}
