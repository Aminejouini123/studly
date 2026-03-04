<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Event;
use App\Service\PomodoroService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class PomodoroServiceTest extends TestCase
{
    public function testGenerateSessionsForEventWithValidDuration(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(5))
            ->method('persist');

        $service = new PomodoroService($entityManager);

        $event = new Event();
        $event->setDuration(60);

        $service->generateSessionsForEvent($event);

        $sessions = array_values($event->getPomodoroSessions()->toArray());

        $this->assertCount(5, $sessions);
        $this->assertSame('WORK', $sessions[0]->getType());
        $this->assertSame(25, $sessions[0]->getDuration());
        $this->assertSame('SHORT_BREAK', $sessions[1]->getType());
        $this->assertSame('WORK', $sessions[4]->getType());
        $this->assertSame(10, $sessions[4]->getDuration());
    }
}

