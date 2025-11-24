<?php

namespace App\Tests\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Service\EventAuthorizationService;
use App\Service\EventManagementService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventManagementServiceTest extends TestCase
{
    private EventRepository&MockObject $eventRepository;
    private EventAuthorizationService&MockObject $authorizationService;
    private EventManagementService $service;

    protected function setUp(): void
    {
        $this->eventRepository = $this->createMock(EventRepository::class);
        $this->authorizationService = $this->createMock(EventAuthorizationService::class);
        $this->service = new EventManagementService(
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

    private function createEvent(?User $owner = null): Event
    {
        $event = new Event();
        if (null !== $owner) {
            $event->setOwner($owner);
        }

        return $event;
    }

    public function testCreateEvent(): void
    {
        $user = $this->createUser(1);
        $event = $this->createEvent();

        $this->eventRepository
            ->expects($this->once())
            ->method('save')
            ->with($event);

        $this->service->createEvent($event, $user);

        $this->assertSame($user, $event->getOwner());
    }

    public function testUpdateEvent(): void
    {
        $user = $this->createUser(1);
        $event = $this->createEvent($user);

        $this->authorizationService
            ->expects($this->once())
            ->method('ensureIsOwner')
            ->with($user, $event);

        $this->eventRepository
            ->expects($this->once())
            ->method('flush');

        $this->service->updateEvent($event, $user);
    }

    public function testUpdateEventThrowsException(): void
    {
        $owner = $this->createUser(1);
        $otherUser = $this->createUser(2);
        $event = $this->createEvent($owner);

        $this->authorizationService
            ->expects($this->once())
            ->method('ensureIsOwner')
            ->with($otherUser, $event)
            ->willThrowException(new \LogicException('Vous ne pouvez effectuer cette action que sur vos propres événements.'));

        $this->eventRepository
            ->expects($this->never())
            ->method('flush');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Vous ne pouvez effectuer cette action que sur vos propres événements.');

        $this->service->updateEvent($event, $otherUser);
    }

    public function testDeleteEvent(): void
    {
        $user = $this->createUser(1);
        $event = $this->createEvent($user);

        $this->authorizationService
            ->expects($this->once())
            ->method('ensureIsOwner')
            ->with($user, $event);

        $this->eventRepository
            ->expects($this->once())
            ->method('remove')
            ->with($event);

        $this->service->deleteEvent($event, $user);
    }

    public function testDeleteEventThrowsException(): void
    {
        $owner = $this->createUser(1);
        $otherUser = $this->createUser(2);
        $event = $this->createEvent($owner);

        $this->authorizationService
            ->expects($this->once())
            ->method('ensureIsOwner')
            ->with($otherUser, $event)
            ->willThrowException(new \LogicException('Vous ne pouvez effectuer cette action que sur vos propres événements.'));

        $this->eventRepository
            ->expects($this->never())
            ->method('remove');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Vous ne pouvez effectuer cette action que sur vos propres événements.');

        $this->service->deleteEvent($event, $otherUser);
    }
}
