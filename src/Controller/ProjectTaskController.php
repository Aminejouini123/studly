<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectTask;
use App\Form\ProjectTaskType;
use App\Repository\ProjectTaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/project-task')]
final class ProjectTaskController extends AbstractController
{
    #[Route('/new/{id}', name: 'app_project_task_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Project $project, Request $request, EntityManagerInterface $entityManager): Response
    {
        $group = $project->getGroup();
        // Only the creator of the group or an admin can add tasks
        if ($group->getCreator() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Only the group creator can add tasks.');
        }

        $projectTask = new ProjectTask();
        $projectTask->setProject($project);
        $projectTask->setStatus(ProjectTask::STATUS_TO_DO);

        $form = $this->createForm(ProjectTaskType::class, $projectTask, [
            'group' => $group,
        ]);
        // Remove project field from form since it's fixed
        $form->remove('project');
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projectTask);
            $entityManager->flush();

            $this->addFlash('success', 'Task created successfully!');
            return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project_task/new.html.twig', [
            'project_task' => $projectTask,
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_task_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, ProjectTask $projectTask, EntityManagerInterface $entityManager): Response
    {
        $group = $projectTask->getProject()->getGroup();
        if ($group->getCreator() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Only the group creator can edit tasks.');
        }

        $form = $this->createForm(ProjectTaskType::class, $projectTask, [
            'group' => $group,
        ]);
        $form->remove('project');

        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Task updated successfully!');
            return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project_task/edit.html.twig', [
            'project_task' => $projectTask,
            'project' => $projectTask->getProject(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/status/{status}', name: 'app_project_task_status', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function updateStatus(ProjectTask $projectTask, string $status, EntityManagerInterface $entityManager): Response
    {
        $group = $projectTask->getProject()->getGroup();
        
        // Check if user is member of the group or creator or admin
        if (!$group->getMembers()->contains($this->getUser()) && 
            $group->getCreator() !== $this->getUser() && 
            !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You are not a member of this group.');
        }

        if (in_array($status, [ProjectTask::STATUS_TO_DO, ProjectTask::STATUS_IN_PROGRESS, ProjectTask::STATUS_DONE])) {
            $projectTask->setStatus($status);
            $entityManager->flush();
            
            $this->addFlash('success', 'Task status updated!');
        }

        return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()]);
    }

    #[Route('/{id}', name: 'app_project_task_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, ProjectTask $projectTask, EntityManagerInterface $entityManager): Response
    {
        $group = $projectTask->getProject()->getGroup();
        if ($group->getCreator() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Only the group creator can delete tasks.');
        }

        if ($this->isCsrfTokenValid('delete'.$projectTask->getId(), $request->request->get('_token'))) {
            $entityManager->remove($projectTask);
            $entityManager->flush();
            $this->addFlash('success', 'Task deleted successfully!');
        }

        return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()], Response::HTTP_SEE_OTHER);
    }
}
