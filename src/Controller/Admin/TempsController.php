<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\Motivation;
use App\Form\EventType;
use App\Form\MotivationType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/temps', name: 'app_admin_temps_')]
#[IsGranted('ROLE_ADMIN')]
class TempsController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('admin/temps/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             // ensure DB non-nullable fields have defaults
            if (null === $event->getDescription()) $event->setDescription('');
            if (null === $event->getType()) $event->setType('');
            if (null === $event->getDuration()) $event->setDuration(0);
            if (null === $event->getLocation()) $event->setLocation('');
            if (null === $event->getDifficulty()) $event->setDifficulty(1);
            
            // Assign current admin user or a specific user if needed? 
            // For now, let's assign the current admin user as the creator/owner
            $event->setUser($this->getUser());

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Event created successfully.');

            return $this->redirectToRoute('app_admin_temps_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/temps/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Event updated successfully.');

            return $this->redirectToRoute('app_admin_temps_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/temps/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
            $this->addFlash('success', 'Event deleted successfully.');
        }

        return $this->redirectToRoute('app_admin_temps_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/motivation', name: 'motivation', methods: ['GET', 'POST'])]
    public function motivation(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $motivation = $event->getMotivation() ?? new Motivation();
        $form = $this->createForm(MotivationType::class, $motivation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $motivation->setEvent($event);
            $event->setMotivation($motivation);
            
            // If new motivation, set user to event owner or current admin
            if (!$motivation->getId()) {
                 $motivation->setUser($event->getUser() ?? $this->getUser());
            }

            $entityManager->persist($motivation);
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Motivation strategy updated successfully.');

            return $this->redirectToRoute('app_admin_temps_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/temps/motivation.html.twig', [
            'event' => $event,
            'motivation' => $motivation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/motivation/delete', name: 'motivation_delete', methods: ['POST'])]
    public function deleteMotivation(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $motivation = $event->getMotivation();
        if ($motivation && $this->isCsrfTokenValid('delete_motivation'.$event->getId(), $request->request->get('_token'))) {
             $event->setMotivation(null);
             $entityManager->persist($event);
             $entityManager->remove($motivation);
             $entityManager->flush();
             $this->addFlash('success', 'Motivation deleted successfully.');
        }

        return $this->redirectToRoute('app_admin_temps_motivation', ['id' => $event->getId()]);
    }
}
