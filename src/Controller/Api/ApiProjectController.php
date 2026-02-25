<?php

namespace App\Controller\Api;

use App\Entity\Project;
use App\Entity\ProjectTask;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/projects', name: 'api_projects_')]
#[IsGranted('ROLE_USER')]
class ApiProjectController extends AbstractController
{
    /**
     * GET /api/projects/{id}
     * Retourne le détail d'un projet.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, ProjectRepository $projectRepository): JsonResponse
    {
        $project = $projectRepository->find($id);

        if (!$project) {
            return $this->json(['error' => 'Projet introuvable.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->serializeProject($project), Response::HTTP_OK);
    }

    /**
     * GET /api/projects/{id}/tasks
     * Retourne la liste des tâches d'un projet.
     */
    #[Route('/{id}/tasks', name: 'tasks', methods: ['GET'])]
    public function tasks(int $id, ProjectRepository $projectRepository): JsonResponse
    {
        $project = $projectRepository->find($id);

        if (!$project) {
            return $this->json(['error' => 'Projet introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $tasks = array_map(fn(ProjectTask $t) => $this->serializeTask($t), $project->getProjectTasks()->toArray());

        return $this->json([
            'projectId' => $project->getId(),
            'title'     => $project->getTitle(),
            'tasks'     => $tasks,
        ], Response::HTTP_OK);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function serializeProject(Project $project): array
    {
        return [
            'id'          => $project->getId(),
            'title'       => $project->getTitle(),
            'description' => $project->getDescription(),
            'status'      => $project->getStatus(),
            'type'        => $project->getType(),
            'resource'    => $project->getResource(),
            'deadline'    => $project->getDeadline()?->format('Y-m-d'),
            'group'       => $project->getGroup() ? [
                'id'       => $project->getGroup()->getId(),
                'category' => $project->getGroup()->getCategory(),
            ] : null,
            'taskCount'   => $project->getProjectTasks()->count(),
        ];
    }

    private function serializeTask(ProjectTask $task): array
    {
        return [
            'id'           => $task->getId(),
            'title'        => $task->getTitle(),
            'description'  => $task->getDescription(),
            'status'       => $task->getStatus(),
            'deadline'     => $task->getDeadline()?->format('Y-m-d H:i:s'),
            'completedAt'  => $task->getCompletedAt()?->format('Y-m-d H:i:s'),
            'assignedUser' => $task->getAssignedUser() ? [
                'id'    => $task->getAssignedUser()->getId(),
                'email' => $task->getAssignedUser()->getEmail(),
            ] : null,
        ];
    }
}
