<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\PomodoroSession;
use Doctrine\ORM\EntityManagerInterface;

class PomodoroService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function generateSessionsForEvent(Event $event): void
    {
        $duration = $event->getDuration();

        // Rule: Only for events > 50 minutes
        if ($duration <= 50) {
            return;
        }

        $remainingMinutes = $duration;
        $workSessionDuration = 25;
        $shortBreakDuration = 5;
        $longBreakDuration = 15;
        $sessionsCount = 0;

        while ($remainingMinutes > 0) {
            // Work Session
            $workTime = min($remainingMinutes, $workSessionDuration);
            $session = new PomodoroSession();
            $session->setType('WORK');
            $session->setDuration($workTime);
            $session->setStatus('PENDING');
            $session->setEvent($event);
            
            $event->addPomodoroSession($session);
            $this->em->persist($session);
            
            $remainingMinutes -= $workTime;
            $sessionsCount++;

            // If no time left, stop
            if ($remainingMinutes <= 0) break;

            // Break Logic
            if ($sessionsCount % 4 == 0) {
                // Long Break
                $breakSession = new PomodoroSession();
                $breakSession->setType('LONG_BREAK');
                $breakSession->setDuration($longBreakDuration);
                $breakSession->setStatus('PENDING');
                $breakSession->setEvent($event);
                
                $event->addPomodoroSession($breakSession);
                $this->em->persist($breakSession);
                
                // Breaks are *additional* to the work duration usually in Pomodoro, 
                // but if Duration is total time block, we subtract. 
                // The prompt says "sessions intercoupées de pauses", implying the pauses are part of the flow.
                // Commonly "Estimated Duration" is "Work Effort". 
                // However, let's assume pauses don't consume the "Work Effort" estimate unless specified.
                // But for safety in scheduling, let's just add them as extra or part of block.
                // Strategy: We won't subtract break time from $remainingMinutes (which tracks work to be done).
                // Wait, if Duration = 60m, and we do 25w, 5b, 25w, 5b... we might exceed 60m total time.
                // Let's assume Duration is the WORK duration required.
            } else {
                // Short Break
                $breakSession = new PomodoroSession();
                $breakSession->setType('SHORT_BREAK');
                $breakSession->setDuration($shortBreakDuration);
                $breakSession->setStatus('PENDING');
                $breakSession->setEvent($event);

                $event->addPomodoroSession($breakSession);
                $this->em->persist($breakSession);
            }
        }
    }
}
