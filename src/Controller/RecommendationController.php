<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Service\RecommendationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recommendations')]
class RecommendationController extends AbstractController
{
    #[Route('/', name: 'app_recommendations')]
    public function index(): Response
    {
        // For simplicity, we can fetch recommendations on load, or expect them in session
        return $this->render('front/recommendations/index.html.twig', [
            'recommendations' => null,
            'loading' => false
        ]);
    }

    #[Route('/generate', name: 'app_recommendations_generate')]
    public function generate(RecommendationService $recommendationService, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Call the FastAPI service to get recommendations
        $recommendations = $recommendationService->getRecommendationsForUser($user);

        if (isset($recommendations['general_summary'])) {
            // Create a notification for the AI summary
            $notification = new Notification();
            $notification->setUser($user);
            // We prepend [AI] to distinguish it
            $notification->setContent('[AI] ' . $recommendations['general_summary']);
            $notification->setLink($this->generateUrl('app_recommendations'));
            $em->persist($notification);
            $em->flush();
        }

        return $this->render('front/recommendations/index.html.twig', [
            'recommendations' => $recommendations,
            'loading' => false
        ]);
    }
}
