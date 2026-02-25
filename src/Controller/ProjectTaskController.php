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

use App\Form\ProjectTaskDeliverableType;
use App\Form\ProjectTaskGradeType;
use App\Form\ProjectTaskAttachmentType;
use App\Service\ScoreService;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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

    #[Route('/{id}/status/{status}', name: 'app_project_task_status', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    public function patchStatus(ProjectTask $projectTask, string $status, EntityManagerInterface $entityManager, ScoreService $scoreService): Response
    {
        // Strict security: Only assigned user or admin
        if ($projectTask->getAssignedUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Seul l\'utilisateur assigné peut changer le statut.'], Response::HTTP_FORBIDDEN);
        }

        if (in_array($status, [ProjectTask::STATUS_TO_DO, ProjectTask::STATUS_IN_PROGRESS, ProjectTask::STATUS_DONE])) {
            $projectTask->setStatus($status);
            
            // Handle completedAt logic
            if ($status === ProjectTask::STATUS_DONE) {
                $projectTask->setCompletedAt(new \DateTime());
            } else {
                $projectTask->setCompletedAt(null);
            }

            $scoreService->updateScoreForProjectTask($projectTask);
            $entityManager->flush();
            
            return $this->json([
                'success' => true,
                'status' => $status,
                'completedAt' => $projectTask->getCompletedAt()?->format('d/m/Y'),
                'badgeClass' => $this->getBadgeClass($status)
            ]);
        }

        return $this->json(['error' => 'Statut invalide'], Response::HTTP_BAD_REQUEST);
    }

    private function getBadgeClass(string $status): string
    {
        return match($status) {
            ProjectTask::STATUS_DONE => 'bg-success',
            ProjectTask::STATUS_IN_PROGRESS => 'bg-warning',
            default => 'bg-secondary',
        };
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

    #[Route('/{id}/upload', name: 'app_project_task_upload', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function uploadDeliverable(Request $request, ProjectTask $projectTask, EntityManagerInterface $entityManager, SluggerInterface $slugger, ScoreService $scoreService): Response
    {
        // Only assigned user can upload
        if ($projectTask->getAssignedUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Only the assigned user can upload a deliverable.');
        }

        $form = $this->createForm(ProjectTaskDeliverableType::class, $projectTask);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $deliverableFile */
            $deliverableFile = $form->get('deliverable')->getData();

            if ($deliverableFile) {
                $originalFilename = pathinfo($deliverableFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$deliverableFile->guessExtension();

                try {
                    $deliverableFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/deliverables',
                        $newFilename
                    );
                    $projectTask->setDeliverable($newFilename);
                    $projectTask->setStatus(ProjectTask::STATUS_DONE);
                    
                    // Clear any previous completedAt just in case
                    $projectTask->setCompletedAt(new \DateTime());
                    
                    // updateScoreForProjectTask will update points
                    $scoreService->updateScoreForProjectTask($projectTask);
                    
                    $entityManager->persist($projectTask);
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Travail déposé avec succès ! La tâche est désormais "Terminée".');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Impossible d\'enregistrer le fichier : ' . $e->getMessage());
                }
            } else {
                $this->addFlash('warning', 'Aucun fichier sélectionné.');
            }

            return $this->redirectToRoute('app_groups_show', ['id' => $projectTask->getProject()->getGroup()->getId()]);
        }

        return $this->render('project_task/upload.html.twig', [
            'project_task' => $projectTask,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/grade', name: 'app_project_task_grade', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function gradeTask(Request $request, ProjectTask $projectTask, EntityManagerInterface $entityManager, ScoreService $scoreService): Response
    {
        $group = $projectTask->getProject()->getGroup();
        // Only creator can grade
        if ($group->getCreator() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Only the group creator can grade tasks.');
        }

        if ($projectTask->getStatus() !== ProjectTask::STATUS_DONE) {
            $this->addFlash('error', 'Vous ne pouvez noter qu\'une tâche terminée.');
            return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()]);
        }

        $oldGrade = $projectTask->getGrade();
        $form = $this->createForm(ProjectTaskGradeType::class, $projectTask);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // If the grade changed, we might want to update the score points?
            // Let's assume the score gets updated based on the grade change.
            if ($projectTask->getAssignedUser()) {
                $pointsDiff = $projectTask->getGrade() - ($oldGrade ?? 0);
                $projectTask->getAssignedUser()->addScore($pointsDiff);
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Note attribuée avec succès !');

            return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()]);
        }

        return $this->render('project_task/grade.html.twig', [
            'project_task' => $projectTask,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/{id}/attachment', name: 'app_project_task_attachment', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function uploadAttachment(Request $request, ProjectTask $projectTask, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $group = $projectTask->getProject()->getGroup();
        
        // Security: Only assigned user can upload
        if ($projectTask->getAssignedUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Seul l\'utilisateur assigné peut ajouter une pièce jointe.');
        }

        $form = $this->createForm(ProjectTaskAttachmentType::class, $projectTask);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $attachmentFile = $form->get('attachment')->getData();

            if ($attachmentFile) {
                $originalFilename = pathinfo($attachmentFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$attachmentFile->guessExtension();

                try {
                    $attachmentFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/tasks',
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du dépôt du fichier.');
                }
                
                // Remove old file if exists
                if ($projectTask->getAttachment()) {
                    $oldPath = $this->getParameter('kernel.project_dir').'/public/uploads/tasks/'.$projectTask->getAttachment();
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $projectTask->setAttachment($newFilename);
                $entityManager->flush();
                $this->addFlash('success', 'Fichier joint avec succès !');
            }

            return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()]);
        }

        return $this->render('project_task/upload_attachment.html.twig', [
            'project_task' => $projectTask,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/download-attachment', name: 'app_project_task_download_attachment', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function downloadAttachment(ProjectTask $projectTask): Response
    {
        $group = $projectTask->getProject()->getGroup();
        
        // Security: Group creator only
        if ($group->getCreator() !== $this->getUser() && 
            !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Seul le créateur du groupe peut télécharger ce fichier.');
        }

        if (!$projectTask->getAttachment()) {
            throw $this->createNotFoundException('Aucun fichier joint.');
        }

        $filePath = $this->getParameter('kernel.project_dir').'/public/uploads/tasks/'.$projectTask->getAttachment();
        
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Fichier physique introuvable.');
        }

        return $this->file($filePath, null, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }
    #[Route('/{id}/download-deliverable', name: 'app_project_task_download_deliverable', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function downloadDeliverable(ProjectTask $projectTask): Response
    {
        $group = $projectTask->getProject()->getGroup();
        
        // Security: Group creator or admin only
        if ($group->getCreator() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Seul le créateur du groupe peut télécharger ce livrable.');
        }

        if (!$projectTask->getDeliverable()) {
            throw $this->createNotFoundException('Aucun livrable déposé.');
        }

        $filePath = $this->getParameter('kernel.project_dir').'/public/uploads/deliverables/'.$projectTask->getDeliverable();
        
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Fichier physique introuvable.');
        }

        return $this->file($filePath, null, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }
}
