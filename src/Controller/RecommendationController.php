<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Service\RecommendationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/generate', name: 'app_recommendations_generate', methods: ['POST', 'GET'])]
    public function generate(Request $request, RecommendationService $recommendationService, EntityManagerInterface $em): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $targetJob = $request->get('targetJob');

        // Call the FastAPI service to get recommendations
        $recommendations = $recommendationService->getRecommendationsForUser($user, $targetJob);

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
            'targetJob' => $targetJob,
            'loading' => false
        ]);
    }

    #[Route('/roadmap/save', name: 'app_recommendations_roadmap_save', methods: ['POST'])]
    public function saveRoadmap(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $roadmapSteps = $data['roadmap'] ?? [];
        $targetJob = $data['targetJob'] ?? 'My Career Roadmap';

        if (empty($roadmapSteps)) {
            return $this->json(['error' => 'No roadmap steps provided'], Response::HTTP_BAD_REQUEST);
        }

        // Create an Objective for the Roadmap
        $objective = new \App\Entity\Objective();
        $objective->setTitle(substr('Career Roadmap: ' . $targetJob, 0, 255));
        $objective->setDescription(substr('AI-generated career roadmap to become a ' . $targetJob, 0, 255));

        $totalWeeks = 0;
        foreach ($roadmapSteps as $step) {
            $totalWeeks += (int) ($step['duration_weeks'] ?? 0);
        }
        $objective->setEstimatedDuration($totalWeeks . ' weeks');
        $objective->setRealDuration(0);
        $objective->setPriority('High');
        $objective->setStatus('To Do');
        $objective->setReason('AI Recommendation Roadmap');

        $em->persist($objective);

        $currentDate = new \DateTime();

        foreach ($roadmapSteps as $index => $step) {
            $task = new \App\Entity\Task();
            $task->setTitle(substr($step['title'] ?? ('Step ' . ($index + 1)), 0, 255));
            $task->setDescription(substr($step['description'] ?? '', 0, 255));
            $task->setRepeatCount(0);
            $task->setStatus('To Do');
            $task->setDifficulty(3); // Default moderate difficulty
            $task->setImpact(5.0); // Default impact
            $task->setAssignedUser($user);
            $task->setObjective($objective);

            $durationWeeks = (int) ($step['duration_weeks'] ?? 1);
            $currentDate->modify("+$durationWeeks weeks");
            $task->setDeadline(clone $currentDate);

            $em->persist($task);
        }

        $em->flush();

        return $this->json(['success' => true, 'message' => 'Roadmap successfully saved as an Objective!']);
    }
}
