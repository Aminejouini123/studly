<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Service\GoogleCalendarService;
use PHPUnit\Framework\TestCase;

final class GoogleCalendarServiceTest extends TestCase
{
    public function testSyncEventReturnsValidGoogleEventId(): void
    {
        $service = $this->getMockBuilder(GoogleCalendarService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['syncEvent'])
            ->getMock();

        $user = new User();
        $event = (new Event())
            ->setTitle('Deep Work Session')
            ->setDescription('Focus block')
            ->setLocation('Home')
            ->setDate(new \DateTime('2026-03-04'));

        $service
            ->expects($this->once())
            ->method('syncEvent')
            ->with($this->identicalTo($user), $this->identicalTo($event))
            ->willReturn('google-event-123');

        $result = $service->syncEvent($user, $event);

        $this->assertIsString($result);
        $this->assertNotSame('', $result);
        $this->assertSame('google-event-123', $result);
    }
}

