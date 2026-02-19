<?php

namespace App\Controller\Api;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/events', name: 'api_events_')]
#[IsGranted('ROLE_USER')]
class EventStatsController extends AbstractController
{
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(EventRepository $eventRepository): Response
    {
        $user = $this->getUser();

        // Compute weekly stats (in minutes) for the current user
        $weeklyMinutes = $eventRepository->getWeeklyDurationMinutesForUser($user);

        $data = [];

        foreach ($weeklyMinutes as $row) {
            $year = (int) $row['year'];
            $week = (int) $row['week'];
            $totalMinutes = (int) $row['totalMinutes'];

            // Compute ISO week start (Monday) and end (Sunday)
            $weekStart = (new \DateTimeImmutable())->setISODate($year, $week);
            $weekEnd   = $weekStart->modify('+6 days');

            $data[] = [
                'week'       => sprintf('%d-W%02d', $year, $week),
                'startDate'  => $weekStart->format('Y-m-d'),
                'endDate'    => $weekEnd->format('Y-m-d'),
                'totalHours' => round($totalMinutes / 60, 2),
            ];
        }

        return $this->json([
            'data' => $data,
        ]);
    }

    #[Route('/calendar', name: 'calendar', methods: ['GET'])]
    public function calendar(EventRepository $eventRepository): Response
    {
        $user = $this->getUser();
        
        // Get all events for the current user
        $events = $eventRepository->findBy(['user' => $user], ['date' => 'ASC', 'startTime' => 'ASC']);
        
        $calendarEvents = [];
        
        foreach ($events as $event) {
            if (!$event->getDate()) {
                continue;
            }
            
            // Use event color or determine from category/type
            $color = $event->getColor();
            if (!$color) {
                $category = $event->getCategory() ?: $event->getType();
                $colorMap = [
                    'Blog Post' => '#3b82f6',
                    'Video' => '#6366f1',
                    'Podcast' => '#10b981',
                    'Exam' => '#ef4444',
                    'Course' => '#8b5cf6',
                    'Meeting' => '#f59e0b',
                    'Task' => '#6b7280',
                ];
                $color = $colorMap[$category] ?? '#3b82f6';
            }
            
            // Build start datetime
            $start = $event->getDate()->format('Y-m-d');
            if ($event->getStartTime()) {
                $start = $event->getStartTime()->format('Y-m-d\TH:i:s');
            }
            
            // Build end datetime
            $end = null;
            if ($event->getEndTime()) {
                $end = $event->getEndTime()->format('Y-m-d\TH:i:s');
            } elseif ($event->getStartTime() && $event->getDuration()) {
                $endTime = clone $event->getStartTime();
                $endTime->modify('+' . $event->getDuration() . ' minutes');
                $end = $endTime->format('Y-m-d\TH:i:s');
            }
            
            $calendarEvents[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'start' => $start,
                'end' => $end,
                'allDay' => $event->isAllDay() ?? false,
                'backgroundColor' => '#ffffff',
                'borderColor' => $color,
                'textColor' => '#1f2937',
                'classNames' => ['calendar-event-card'],
                'extendedProps' => [
                    'description' => $event->getDescription(),
                    'type' => $event->getType(),
                    'category' => $event->getCategory(),
                    'color' => $color,
                    'duration' => $event->getDuration(),
                    'location' => $event->getLocation(),
                    'status' => $event->getStatus(),
                    'priority' => $event->getPriority(),
                    'difficulty' => $event->getDifficulty(),
                    'notes' => $event->getNotes(),
                ],
            ];
        }
        
        return $this->json($calendarEvents);
    }
}

