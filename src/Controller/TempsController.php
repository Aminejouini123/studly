<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class TempsController extends AbstractController
{
    #[Route('/temps', name: 'app_temps')]
    public function index(EventRepository $eventRepository, Request $request, EntityManagerInterface $em, \App\Service\PomodoroService $pomodoroService): Response
    {
        $user = $this->getUser();
        $sort = $request->query->get('sort');

        if ($sort === 'priority') {
            $events = $eventRepository->findByUserSortedByPriority($user);
        } else {
            // Default sort by date
            $events = $eventRepository->findBy(['user' => $user], ['date' => 'ASC']);
        }

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ensure DB non-nullable fields have defaults to avoid constraint errors
            if (null === $event->getDescription()) {
                $event->setDescription('');
            }
            if (null === $event->getType()) {
                $event->setType('');
            }
            if (null === $event->getDuration()) {
                $event->setDuration(0);
            }
            if (null === $event->getLocation()) {
                $event->setLocation('');
            }
            if (null === $event->getDifficulty()) {
                $event->setDifficulty(1);
            }

            $event->setUser($user);
            
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

        $completedCount = 0;
        foreach ($events as $e) {
            if ($e->getStatus() === 'Completed' || $e->getStatus() === 'Terminé') {
                $completedCount++;
            }
        }

        return $this->render('temps/index.html.twig', [
            'events' => $events,
            'form' => $form->createView(),
            'completed_count' => $completedCount,
        ]);
    }

    #[Route('/temps/{id}/edit', name: 'app_temps_edit')]
    public function edit(Event $event, Request $request, EntityManagerInterface $em, \App\Service\PomodoroService $pomodoroService): Response
    {
        $this->denyAccessUnlessGranted('edit', $event);

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
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

    #[Route('/temps/{id}/delete', name: 'app_temps_delete', methods: ['POST'])]
    public function delete(Event $event, EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('delete', $event);

        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $token)) {
            $em->remove($event);
            $em->flush();
            $this->addFlash('success', 'Event deleted successfully!');
        }

        return $this->redirectToRoute('app_temps');
    }

    #[Route('/temps/calendar', name: 'app_temps_calendar')]
    public function calendar(EventRepository $eventRepository): Response
    {
        $user = $this->getUser();
        $events = $eventRepository->findBy(['user' => $user], ['date' => 'ASC']);

        return $this->render('temps/calendar.html.twig', [
            'events' => $events,
        ]);
    }


}
