<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[IsGranted('ROLE_USER')]
final class TempsController extends AbstractController
{
    #[Route('/temps', name: 'app_temps')]
    public function index(
        EventRepository $eventRepository, 
        Request $request, 
        EntityManagerInterface $em, 
        \App\Service\PomodoroService $pomodoroService,
        \App\Service\GoogleCalendarService $calendarService,
        #[Autowire(service: 'cache.app')] CacheInterface $cache
    ): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException();
        }

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setUser($user);
            
            // Sync with Google if connected
            if ($user->getGoogleAccessToken()) {
                try {
                    $googleId = $calendarService->syncEvent($user, $event);
                    $event->setGoogleEventId($googleId);
                } catch (\Exception $e) {
                    // Log or handle sync error
                }
            }
            
            // Auto-generate Pomodoro sessions
            $pomodoroService->generateSessionsForEvent($event);

            $em->persist($event);

            $em->flush();
            $this->addFlash('success', 'Event "' . $event->getTitle() . '" created successfully!');
            return $this->redirectToRoute('app_temps');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            // collect field error messages for quick debugging
            $errors = [];
            foreach ($form as $child) {
                foreach ($child->getErrors(true) as $err) {
                    $errors[] = $child->getName() . ': ' . $err->getMessage();
                }
            }
            if (count($errors) > 0) {
                $this->addFlash('error', implode(' | ', $errors));
            } else {
                $this->addFlash('error', 'Form is invalid.');
            }
        }

        $sort = $request->query->get('sort');
        if ($sort === 'priority') {
            $events = $eventRepository->findByUserSortedByPriority($user);
        } else {
            // Default sort by date
            $events = $eventRepository->findBy(['user' => $user], ['date' => 'ASC']);
        }

        $googleEvents = [];
        if ($user->getGoogleAccessToken()) {
            try {
                // Cache short-lived weekly Google events to reduce repeated API latency.
                $weekStart = (new \DateTimeImmutable('monday this week'))->format('Y-m-d');
                $weekEnd = (new \DateTimeImmutable('sunday this week'))->format('Y-m-d');
                $cacheKey = sprintf('temps_google_week_%d_%s_%s', (int) $user->getId(), $weekStart, $weekEnd);

                $googleEvents = $cache->get($cacheKey, function (ItemInterface $item) use ($calendarService, $user): array {
                    $item->expiresAfter(120);
                    $timeMin = (new \DateTime('monday this week'))->format(\DateTime::RFC3339);
                    $timeMax = (new \DateTime('sunday this week'))->format(\DateTime::RFC3339);
                    return $calendarService->listEvents($user, $timeMin, $timeMax, 50);
                });
            } catch (\Exception $e) {
                // Silently fail or log if Google API is unreachable
            }
        }

        $completedCount = 0;
        foreach ($events as $e) {
            if ($e->getStatus() === 'Completed' || $e->getStatus() === 'Terminé') {
                $completedCount++;
            }
        }

        return $this->render('temps/index.html.twig', [
            'events' => $events,
            'google_events' => $googleEvents,
            'form' => $form->createView(),
            'completed_count' => $completedCount,
        ]);
    }

    #[Route('/temps/{id}/edit', name: 'app_temps_edit')]
    public function edit(
        Event $event, 
        Request $request, 
        EntityManagerInterface $em, 
        \App\Service\PomodoroService $pomodoroService,
        \App\Service\GoogleCalendarService $calendarService
    ): Response
    {
        $this->denyAccessUnlessGranted('edit', $event);
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // Sync with Google if connected
            if ($user->getGoogleAccessToken()) {
                try {
                    $googleId = $calendarService->syncEvent($user, $event);
                    $event->setGoogleEventId($googleId);
                } catch (\Exception $e) {
                    // Log sync error
                }
            }

            // Regenerate/Update Pomodoro sessions if duration changed
            $pomodoroService->generateSessionsForEvent($event);

            $em->flush();
            $this->addFlash('success', 'Event updated successfully!');
            return $this->redirectToRoute('app_temps');
        }

        return $this->render('temps/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/temps/{id}/sync', name: 'app_temps_sync')]
    public function sync(
        Event $event, 
        EntityManagerInterface $em, 
        \App\Service\GoogleCalendarService $calendarService
    ): Response
    {
        $this->denyAccessUnlessGranted('edit', $event);
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException();
        }

        if ($user->getGoogleAccessToken()) {
            try {
                $googleId = $calendarService->syncEvent($user, $event);
                $event->setGoogleEventId($googleId);
                $em->flush();
                $this->addFlash('success', 'Event synchronized with Google Calendar!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error during synchronization: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Please link your Google account first.');
        }

        return $this->redirectToRoute('app_temps');
    }

    #[Route('/temps/{id}/delete', name: 'app_temps_delete', methods: ['POST'])]
    public function delete(
        Event $event, 
        EntityManagerInterface $em, 
        Request $request,
        \App\Service\GoogleCalendarService $calendarService
    ): Response
    {
        $this->denyAccessUnlessGranted('delete', $event);
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException();
        }

        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $token)) {
            
            // Delete from Google if linked
            if ($event->getGoogleEventId() && $user->getGoogleAccessToken()) {
                try {
                    $calendarService->deleteEvent($user, $event->getGoogleEventId());
                } catch (\Exception $e) {
                    // Log or handle error (404 is fine as it's already gone)
                }
            }

            $em->remove($event);
            $em->flush();
            $this->addFlash('success', 'Event deleted successfully!');
        }

        return $this->redirectToRoute('app_temps');
    }

    #[Route('/temps/analyze', name: 'app_temps_analyze', methods: ['POST'])]
    public function analyze(
        Request $request, 
        \App\Service\SmartPlanningService $planningService,
        EventRepository $eventRepository
    ): Response
    {
        $user = $this->getUser();
        $energy = (int)$request->request->get('energy', 5);
        $stress = (int)$request->request->get('stress', 5);
        $sleep = (int)$request->request->get('sleep', 5);
        $mood = $request->request->get('mood', '');

        // Fetch user's non-completed events to optimize
        $events = $eventRepository->findBy(['user' => $user], ['date' => 'ASC']);
        $tasksData = [];
        foreach ($events as $event) {
            if ($event->getStatus() !== 'Completed' && $event->getStatus() !== 'Terminé') {
                $tasksData[] = [
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                    'difficulty' => $event->getDifficulty(),
                    'initial_duration' => $event->getDuration()
                ];
            }
        }

        try {
            $userState = [
                'energy' => $energy,
                'stress' => $stress,
                'sleep_quality' => $sleep,
                'mood_text' => $mood,
                'date' => (new \DateTime())->format('d-m-Y')
            ];

            $result = $planningService->analyze($userState, $tasksData);

            if ($result['status'] === 'success') {
                $this->addFlash('success', 'Intelligence Artificielle : Planning optimisé avec succès (Niveau de motivation : ' . $result['motivation']['level'] . ')');
                
                // Store path in session to allow download
                $request->getSession()->set('last_planning_pdf', $result['pdf_path']);
                
                return $this->redirectToRoute('app_temps');
            } else {
                $this->addFlash('error', 'Erreur IA : ' . ($result['message'] ?? 'Erreur inconnue'));
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur de service : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_temps');
    }

    #[Route('/temps/download-planning', name: 'app_temps_download_planning')]
    public function downloadPlanning(Request $request): Response
    {
        $pdfPath = $request->getSession()->get('last_planning_pdf');

        if (!$pdfPath || !file_exists($pdfPath)) {
            $this->addFlash('error', 'Aucun planning généré récemment.');
            return $this->redirectToRoute('app_temps');
        }

        return $this->file($pdfPath, 'planning_etudiant.pdf');
    }

}
