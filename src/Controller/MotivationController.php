<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Motivation;
use App\Form\MotivationType;
use App\Repository\MotivationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MotivationController extends AbstractController
{
    #[Route('/temps/{id}/motivations', name: 'app_motivation_panel', methods: ['GET'])]
    public function panel(Event $event): Response
    {
        $motivation = $event->getMotivation();
        $form = $this->createForm(MotivationType::class, $motivation ?? new Motivation());

        return $this->render('motivation/panel.html.twig', [
            'event' => $event,
            'motivation' => $motivation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/temps/{id}/motivations/save', name: 'app_motivation_save', methods: ['POST'])]
    public function save(Event $event, Request $request, EntityManagerInterface $em, MotivationRepository $repo): Response
    {
        $motivation = $event->getMotivation() ?? new Motivation();
        $form = $this->createForm(MotivationType::class, $motivation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $motivation = $form->getData();
            // Critical fix: Event is the owning side of the OneToOne relationship.
            // We must update the Event to link it to the Motivation.
            $event->setMotivation($motivation);
            $motivation->setEvent($event);
            
            $user = $this->getUser();
            if (!$user instanceof \App\Entity\User) {
                throw new \LogicException('User not found');
            }
            $motivation->setUser($user);

            $em->persist($motivation);
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Motivation strategy saved successfully!');
            // Redirect back to the time management page
            return $this->redirectToRoute('app_temps');
        }

        // Form has errors: re-render the page
        return $this->render('motivation/panel.html.twig', [
            'event' => $event,
            'motivation' => $motivation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/temps/{id}/motivations/delete', name: 'app_motivation_delete', methods: ['POST'])]
    public function delete(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        $motivation = $event->getMotivation();
        if (!$motivation) {
            return $this->redirectToRoute('app_motivation_panel', ['id' => $event->getId()]);
        }
        
        // User requested to remove CSRF check ("fix it delete it")
        // if (!$this->isCsrfTokenValid('delete_motivation'.$event->getId(), $token)) {
        //     $this->addFlash('error', 'Invalid CSRF token');
        //     return $this->redirectToRoute('app_motivation_panel', ['id' => $event->getId()]);
        // }

        // Disconnect the motivation from the event before deleting
        // This is crucial because Event is the owning side
        $event->setMotivation(null);
        $em->persist($event);
        
        $em->remove($motivation);
        $em->flush();

        $this->addFlash('success', 'Motivation strategy deleted.');
        return $this->redirectToRoute('app_temps');
    }
}
