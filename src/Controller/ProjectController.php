<?php

declare(strict_types=1);


namespace App\Controller;

use App\Entity\Group;
use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/project')]
final class ProjectController extends AbstractController
{
    #[Route('/new/{group}', name: 'app_project_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Group $group, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('User not found');
        }
        // Only the creator of the group or an admin can add projects
        if ($group->getCreator() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Only the group creator can add projects.');
        }

        $project = new Project();
        $project->setGroup($group);
        $project->setStatus('PENDING'); // Default status

        $form = $this->createForm(ProjectType::class, $project);
        $form->remove('group');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $resourceFile */
            $resourceFile = $form->get('resource')->getData();

            if ($resourceFile) {
                $originalFilename = pathinfo($resourceFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$resourceFile->guessExtension();

                try {
                    $resourceFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/projects',
                        $newFilename
                    );
                    $project->setResource($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Could not upload file.');
                }
            }

            $entityManager->persist($project);
            $entityManager->flush();

            $this->addFlash('success', 'Project created successfully!');
            return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project/new.html.twig', [
            'project' => $project,
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('User not found');
        }
        $group = $project->getGroup();
        if ($group->getCreator() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Only the group creator can edit projects.');
        }

        $form = $this->createForm(ProjectType::class, $project);
        $form->remove('group');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $resourceFile */
            $resourceFile = $form->get('resource')->getData();

            if ($resourceFile) {
                $originalFilename = pathinfo($resourceFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$resourceFile->guessExtension();

                try {
                    $resourceFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/projects',
                        $newFilename
                    );
                    
                    // Remove old file
                    if ($project->getResource()) {
                        $oldFilePath = $this->getParameter('kernel.project_dir').'/public/uploads/projects/'.$project->getResource();
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                    
                    $project->setResource($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Could not upload file.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Project updated successfully!');
            return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_project_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $group = $project->getGroup();
        if ($group->getCreator() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Only the group creator can delete projects.');
        }

        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            // Remove file
            if ($project->getResource()) {
                $filePath = $this->getParameter('kernel.project_dir').'/public/uploads/projects/'.$project->getResource();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $entityManager->remove($project);
            $entityManager->flush();
            $this->addFlash('success', 'Project deleted successfully!');
        }

        return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()], Response::HTTP_SEE_OTHER);
    }
}
