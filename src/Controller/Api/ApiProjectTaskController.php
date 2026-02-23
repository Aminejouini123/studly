<?php

namespace App\Controller\Api;

use App\Entity\ProjectTask;
use App\Repository\ProjectTaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/project-task', name: 'api_project_task_')]
#[IsGranted('ROLE_USER')]
class ApiProjectTaskController extends AbstractController
{
    /** Statuts autorisés */
    private const VALID_STATUSES = [
        ProjectTask::STATUS_TO_DO,
        ProjectTask::STATUS_IN_PROGRESS,
        ProjectTask::STATUS_DONE,
    ];

    /**
     * PATCH /api/project-task/{id}/status
     * Met à jour le statut d'une tâche.
     * Body JSON attendu : { "status": "IN_PROGRESS" }
     */
    #[Route('/{id}/status', name: 'update_status', methods: ['PATCH'])]
    public function updateStatus(
        int $id,
        Request $request,
        ProjectTaskRepository $taskRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $task = $taskRepository->find($id);

        if (!$task) {
            return $this->json(['error' => 'Tâche introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $body = json_decode($request->getContent(), true);

        if (empty($body['status'])) {
            return $this->json(['error' => 'Le champ "status" est obligatoire.'], Response::HTTP_BAD_REQUEST);
        }

        if (!in_array($body['status'], self::VALID_STATUSES, true)) {
            return $this->json([
                'error'           => 'Statut invalide.',
                'allowed_statuses' => self::VALID_STATUSES,
            ], Response::HTTP_BAD_REQUEST);
        }

        $oldStatus = $task->getStatus();
        $task->setStatus($body['status']);

        // Mettre à jour completedAt si la tâche passe à DONE
        if ($body['status'] === ProjectTask::STATUS_DONE && $oldStatus !== ProjectTask::STATUS_DONE) {
            $task->setCompletedAt(new \DateTime());
        } elseif ($body['status'] !== ProjectTask::STATUS_DONE) {
            $task->setCompletedAt(null);
        }

        $em->flush();

        return $this->json([
            'message'    => 'Statut mis à jour avec succès.',
            'id'         => $task->getId(),
            'status'     => $task->getStatus(),
            'completedAt' => $task->getCompletedAt()?->format('Y-m-d H:i:s'),
        ], Response::HTTP_OK);
    }

    /**
     * POST /api/project-task/{id}/assign
     * Assigne un utilisateur à une tâche.
     * Body JSON attendu : { "userId": 42 }
     */
    #[Route('/{id}/assign', name: 'assign', methods: ['POST'])]
    public function assign(
        int $id,
        Request $request,
        ProjectTaskRepository $taskRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $task = $taskRepository->find($id);

        if (!$task) {
            return $this->json(['error' => 'Tâche introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $body = json_decode($request->getContent(), true);

        if (empty($body['userId'])) {
            return $this->json(['error' => 'Le champ "userId" est obligatoire.'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($body['userId']);

        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $task->setAssignedUser($user);
        $em->flush();

        return $this->json([
            'message'      => 'Tâche assignée avec succès.',
            'taskId'       => $task->getId(),
            'assignedUser' => [
                'id'    => $user->getId(),
                'email' => $user->getEmail(),
            ],
        ], Response::HTTP_OK);
    }
}
