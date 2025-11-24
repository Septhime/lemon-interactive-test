<?php

namespace App\Tests\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Service\EventAuthorizationService;
use PHPUnit\Framework\TestCase;

class EventAuthorizationServiceTest extends TestCase
{
    private EventAuthorizationService $service;

    protected function setUp(): void
    {
        $this->service = new EventAuthorizationService();
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

    public function testIsOwnerReturnsTrue(): void
    {
        $user = $this->createUser(1);
        $event = $this->createEvent($user);

        $result = $this->service->isOwner($user, $event);

        $this->assertTrue($result);
    }

    public function testIsOwnerReturnsFalse(): void
    {
        $owner = $this->createUser(1);
        $otherUser = $this->createUser(2);
        $event = $this->createEvent($owner);

        $result = $this->service->isOwner($otherUser, $event);

        $this->assertFalse($result);
    }

    public function testEnsureIsOwnerDoesNotThrow(): void
    {
        $user = $this->createUser(1);
        $event = $this->createEvent($user);

        $this->service->ensureIsOwner($user, $event);

        $this->expectNotToPerformAssertions();
    }

    public function testEnsureIsOwnerThrowsLogicException(): void
    {
        $owner = $this->createUser(1);
        $otherUser = $this->createUser(2);
        $event = $this->createEvent($owner);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Vous ne pouvez effectuer cette action que sur vos propres événements.');

        $this->service->ensureIsOwner($otherUser, $event);
    }

    public function testCanSubscribeReturnsTrue(): void
    {
        $owner = $this->createUser(1);
        $user = $this->createUser(2);
        $event = $this->createEvent($owner);

        $result = $this->service->canSubscribe($user, $event);

        $this->assertTrue($result);
    }

    public function testCanSubscribeReturnsFalseWhenUserIsOwner(): void
    {
        $user = $this->createUser(1);
        $event = $this->createEvent($user);

        $result = $this->service->canSubscribe($user, $event);

        $this->assertFalse($result);
    }

    public function testCanSubscribeReturnsFalseWhenUserIsAlreadyParticipant(): void
    {
        $owner = $this->createUser(1);
        $user = $this->createUser(2);
        $event = $this->createEvent($owner);
        $event->addParticipant($user);

        $result = $this->service->canSubscribe($user, $event);

        $this->assertFalse($result);
    }
}
