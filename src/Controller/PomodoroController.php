<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\PomodoroSession;
use App\Repository\PomodoroSessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/temps/{id}/pomodoro')]
class PomodoroController extends AbstractController
{
    #[Route('/', name: 'app_event_pomodoro', methods: ['GET', 'POST'])]
    public function index(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        // Security check: ensure user owns the event
        if ($event->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Handle quick add session form if needed, or just list
        // For now, let's just show the list and timer. 
        // We can add a "Add Session" modal or form later if strictly asked for CRUD *interface*.
        // The user asked for "full CRUD interface". 
        // Let's create a simple form to add a manual session.
        
        $session = new PomodoroSession();
        $session->setEvent($event);
        $session->setDuration(25);
        $session->setType('WORK');
        
        // We'll handle the form in a separate route or modal usually, 
        // but for a simple "page", we can put a create form on the side or bottom.
        
        return $this->render('pomodoro/index.html.twig', [
            'event' => $event,
            'sessions' => $event->getPomodoroSessions(),
        ]);
    }

    #[Route('/new', name: 'app_pomodoro_new', methods: ['POST'])]
    public function new(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        if ($event->getUser() !== $this->getUser()) {
             throw $this->createAccessDeniedException();
        }

        $type = $request->request->get('type', 'WORK');
        $duration = $request->request->get('duration', 25);

        $session = new PomodoroSession();
        $session->setEvent($event);
        $session->setType($type);
        $session->setDuration((int)$duration);
        $session->setStatus('PENDING');

        $em->persist($session);
        $em->flush();

        return $this->redirectToRoute('app_event_pomodoro', ['id' => $event->getId()]);
    }

    #[Route('/{sessionId}/delete', name: 'app_pomodoro_delete', methods: ['POST'])]
    public function delete(Event $event, int $sessionId, EntityManagerInterface $em): Response
    {
        if ($event->getUser() !== $this->getUser()) {
             throw $this->createAccessDeniedException();
        }
        
        $session = $em->getRepository(PomodoroSession::class)->find($sessionId);
        if ($session && $session->getEvent() === $event) {
            $em->remove($session);
            $em->flush();
        }

        return $this->redirectToRoute('app_event_pomodoro', ['id' => $event->getId()]);
    }
    
    #[Route('/{sessionId}/status', name: 'app_pomodoro_status', methods: ['POST'])]
    public function updateStatus(Event $event, int $sessionId, Request $request, EntityManagerInterface $em): Response
    {
        if ($event->getUser() !== $this->getUser()) {
             throw $this->createAccessDeniedException();
        }
        
        $session = $em->getRepository(PomodoroSession::class)->find($sessionId);
        $status = $request->request->get('status');
        
        if ($session && $session->getEvent() === $event && in_array($status, ['PENDING', 'IN_PROGRESS', 'COMPLETED'])) {
            $session->setStatus($status);
            $em->flush();
        }

        return $this->redirectToRoute('app_event_pomodoro', ['id' => $event->getId()]);
    }
}
