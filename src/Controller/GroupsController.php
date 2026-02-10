<?php

namespace App\Controller;

use App\Entity\Group;
use App\Form\GroupType;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/groups')]
final class GroupsController extends AbstractController
{
    /**
     * Front Office: Show only groups created by the current student
     */
    #[Route('/', name: 'app_groups_index', methods: ['GET'])]
    #[IsGranted('ROLE_ETUDIANT')]
    public function index(GroupRepository $groupRepository): Response
    {
        $user = $this->getUser();
        
        $groups = $groupRepository->findByCreator($user);

        return $this->render('groups/frontGroups.html.twig', [
            'groups' => $groups,
        ]);
    }

    /**
     * Front Office: Create a new group (students only)
     */
    #[Route('/new', name: 'app_groups_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ETUDIANT')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the current user as the creator
            $group->setCreator($this->getUser());
            
            $entityManager->persist($group);
            $entityManager->flush();

            $this->addFlash('success', 'Group created successfully!');
            return $this->redirectToRoute('app_groups_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('groups/frontGroups_new.html.twig', [
            'group' => $group,
            'form' => $form,
        ]);
    }

    /**
     * Back Office: Show all groups (admins only)
     */
    #[Route('/admin', name: 'app_admin_groups_index', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminIndex(GroupRepository $groupRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create form for the modal
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the current admin as the creator
            $group->setCreator($this->getUser());
            
            $entityManager->persist($group);
            $entityManager->flush();

            $this->addFlash('success', 'Group created successfully!');
            return $this->redirectToRoute('app_admin_groups_index', [], Response::HTTP_SEE_OTHER);
        }
        $searchTerm = $request->query->get('q');
        $sort = $request->query->get('sort');
        $direction = $request->query->get('direction', 'ASC');

        if ($searchTerm) {
            $groups = $groupRepository->searchByCategory($searchTerm);
        } elseif ($sort) {
            $groups = $groupRepository->findAllSorted($sort, $direction);
        } else {
            $groups = $groupRepository->findAllOrderedByCreation();
        }

        return $this->render('groups/backGroups.html.twig', [
            'groups' => $groups,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Back Office: Export groups to CSV (admins only)
     */
    #[Route('/admin/export', name: 'app_admin_groups_export', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminExport(GroupRepository $groupRepository): Response
    {
        $groups = $groupRepository->findAllOrderedByCreation();
        
        $fp = fopen('php://temp', 'w');
        
        // Add BOM for Excel compatibility
        fputs($fp, "\xEF\xBB\xBF");
        
        // Header
        fputcsv($fp, ['ID', 'Category', 'Capacity', 'Creator Email', 'Created At', 'Members Count']);
        
        foreach ($groups as $group) {
            fputcsv($fp, [
                $group->getId(),
                $group->getCategory(),
                $group->getCapacity(),
                $group->getCreator() ? $group->getCreator()->getEmail() : 'Unknown',
                $group->getCreatedAt() ? $group->getCreatedAt()->format('Y-m-d H:i:s') : '',
                $group->getMemberGroup() ? 'Assigned' : 'Unassigned' // Simplified for now as MemberGroup seems to be 1-to-1 or similar
            ]);
        }
        
        rewind($fp);
        $response = new Response(stream_get_contents($fp));
        fclose($fp);
        
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="groups_export.csv"');
        
        return $response;
    }

    /**
     * Back Office: Create a new group (admins only)
     */
    #[Route('/admin/new', name: 'app_admin_groups_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the current admin as the creator
            $group->setCreator($this->getUser());
            
            $entityManager->persist($group);
            $entityManager->flush();

            $this->addFlash('success', 'Group created successfully!');
            return $this->redirectToRoute('app_admin_groups_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('groups/back_groups_new.html.twig', [
            'group' => $group,
            'form' => $form,
        ]);
    }

    /**
     * Back Office: Export groups to PDF (admins only)
     */
    #[Route('/admin/export/pdf', name: 'app_admin_groups_export_pdf', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminExportPdf(GroupRepository $groupRepository): Response
    {
        $groups = $groupRepository->findAllOrderedByCreation();

        $html = $this->renderView('groups/back_groups_pdf.html.twig', [
            'groups' => $groups,
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="groups_export.pdf"',
            ]
        );
    }



    /**
     * Back Office: Show group details (admins only)
     */
    #[Route('/admin/{id}', name: 'app_admin_groups_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminShow(Group $group): Response
    {
        return $this->render('groups/back_groups_show.html.twig', [
            'group' => $group,
        ]);
    }

    /**
     * Show group details (both student and admin can view)
     */
    #[Route('/{id}', name: 'app_groups_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Group $group): Response
    {
        $user = $this->getUser();

        // Students can only view their own groups
        if (!$this->isGranted('ROLE_ADMIN') && $group->getCreator() !== $user) {
            throw $this->createAccessDeniedException('You can only view your own groups.');
        }

        return $this->render('groups/show.html.twig', [
            'group' => $group,
        ]);
    }

    /**
     * Front Office: Edit a group (students can only edit their own groups)
     */
    #[Route('/{id}/edit', name: 'app_groups_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ETUDIANT')]
    public function edit(Request $request, Group $group, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Ensure student can only edit their own groups
        if ($group->getCreator() !== $user) {
            throw $this->createAccessDeniedException('You can only edit your own groups.');
        }

        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Group updated successfully!');
            return $this->redirectToRoute('app_groups_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('groups/frontGroups_edit.html.twig', [
            'group' => $group,
            'form' => $form,
        ]);
    }

    /**
     * Back Office: Edit a group (admins can edit any group)
     */
    #[Route('/admin/{id}/edit', name: 'app_admin_groups_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEdit(Request $request, Group $group, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Group updated successfully!');
            return $this->redirectToRoute('app_admin_groups_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('groups/back_groups_edit.html.twig', [
            'group' => $group,
            'form' => $form,
        ]);
    }

    /**
     * Front Office: Delete a group (students can only delete their own groups)
     */
    #[Route('/{id}', name: 'app_groups_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ETUDIANT')]
    public function delete(Request $request, Group $group, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Ensure student can only delete their own groups
        if ($group->getCreator() !== $user) {
            throw $this->createAccessDeniedException('You can only delete your own groups.');
        }

        if ($this->isCsrfTokenValid('delete'.$group->getId(), $request->request->get('_token'))) {
            $entityManager->remove($group);
            $entityManager->flush();

            $this->addFlash('success', 'Group deleted successfully!');
        }

        return $this->redirectToRoute('app_groups_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Back Office: Delete a group (admins can delete any group)
     */
    #[Route('/admin/{id}', name: 'app_admin_groups_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDelete(Request $request, Group $group, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$group->getId(), $request->request->get('_token'))) {
            $entityManager->remove($group);
            $entityManager->flush();

            $this->addFlash('success', 'Group deleted successfully!');
        }

        return $this->redirectToRoute('app_admin_groups_index', [], Response::HTTP_SEE_OTHER);
    }
}
