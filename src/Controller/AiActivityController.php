<?php

namespace App\Controller;

use App\Repository\CourseRepository;
use App\Service\AiActivityGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class AiActivityController extends AbstractController
{
    #[Route('/ai/generate-activity/{courseId}', name: 'api_generate_activity', methods: ['POST'])]
    public function generate(
        int $courseId,
        CourseRepository $courseRepository,
        AiActivityGeneratorService $aiService
    ): JsonResponse {
        $course = $courseRepository->find($courseId);

        if (!$course) {
            return $this->json(['error' => 'Course not found'], 404);
        }

        try {
            $activity = $aiService->generateActivityForCourse($course);

            if (!$activity) {
                return $this->json(['error' => 'AI failed to generate activity'], 500);
            }

            return $this->json([
                'success' => true,
                'activity' => [
                    'id' => $activity->getId(),
                    'title' => $activity->getTitle(),
                    'description' => $activity->getDescription(),
                    'duration' => $activity->getDuration(),
                    'type' => $activity->getType(),
                    'difficulty' => $activity->getDifficulty(),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
