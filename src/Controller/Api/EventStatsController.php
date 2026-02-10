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
}

