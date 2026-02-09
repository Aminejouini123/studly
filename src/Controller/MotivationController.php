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
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/temps")
 */
class MotivationController extends AbstractController
{
    #[Route('/{id}/motivations', name: 'app_motivation_panel', methods: ['GET'])]
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

    #[Route('/{id}/motivations/save', name: 'app_motivation_save', methods: ['POST'])]
    public function save(Event $event, Request $request, EntityManagerInterface $em, MotivationRepository $repo): Response
    {
        $motivation = $event->getMotivation() ?? new Motivation();
        $form = $this->createForm(MotivationType::class, $motivation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $motivation = $form->getData();
            $motivation->setEvent($event);
            if ($this->getUser()) $motivation->setUser($this->getUser());
            $em->persist($motivation);
            $em->flush();
            if ($request->isXmlHttpRequest()) {
                // return updated panel
                $form = $this->createForm(MotivationType::class, $motivation);
                return $this->render('motivation/panel.html.twig', [
                    'event' => $event,
                    'motivation' => $motivation,
                    'form' => $form->createView(),
                ]);
            }

            $this->addFlash('success', 'Motivation saved');
            return $this->redirectToRoute('app_temps');
        }

        // invalid
        if ($request->isXmlHttpRequest()) {
            return $this->render('motivation/panel.html.twig', [
                'event' => $event,
                'motivation' => $motivation,
                'form' => $form->createView(),
            ]);
        }

        $this->addFlash('error', 'Could not save motivation.');
        return $this->redirectToRoute('app_temps');
    }

    #[Route('/{id}/motivations/delete', name: 'app_motivation_delete', methods: ['POST'])]
    public function delete(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        $motivation = $event->getMotivation();
        if (!$motivation) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_motivation'.$event->getId(), $token)) {
            return $this->json(['error' => 'Invalid CSRF'], 403);
        }

        $em->remove($motivation);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return $this->json(['success' => true]);
        }

        $this->addFlash('success', 'Motivation deleted');
        return $this->redirectToRoute('app_temps');
    }
}
