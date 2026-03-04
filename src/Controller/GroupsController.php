<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Invitation;
use App\Entity\Notification;
use App\Entity\User;
use App\Form\GroupType;
use App\Repository\GroupRepository;
use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use App\Service\ScoreService;
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
    public function index(GroupRepository $groupRepository, Request $request): Response
    {
        $user = $this->getUser();
        $searchTerm = $request->query->get('q');

        if ($searchTerm) {
            // For search, we might want to see all groups OR just ours?
            // The story says "Un étudiant peut... rechercher et lister des groupes".
            // Let's assume they search within their own groups first, or globally?
            // Usually search is global. Let's make it global but filter by creator if no search.
            $groups = $groupRepository->searchByCategory($searchTerm);
        } else {
            $groups = $groupRepository->findUserGroups($user);
        }

        $assignedCount = 0;
        $totalCapacity = 0;
        foreach ($groups as $g) {
            if (!$g->getMembers()->isEmpty()) {
                $assignedCount++;
            }
            $totalCapacity += $g->getCapacity();
        }

        return $this->render('groups/frontGroups.html.twig', [
            'groups' => $groups,
            'assigned_count' => $assignedCount,
            'total_capacity' => $totalCapacity,
            'search_term' => $searchTerm,
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
                !$group->getMembers()->isEmpty() ? 'Assigned' : 'Unassigned'
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
    #[Route('/invitations', name: 'app_groups_invitations', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function listInvitations(InvitationRepository $invitationRepository): Response
    {
        $invitations = $invitationRepository->findBy(['receiver' => $this->getUser(), 'status' => Invitation::STATUS_PENDING]);

        return $this->render('groups/invitations.html.twig', [
            'invitations' => $invitations,
        ]);
    }

    #[Route('/invitations/{id}/accept', name: 'app_groups_invitation_accept', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function acceptInvitation(Invitation $invitation, EntityManagerInterface $entityManager): Response
    {
        if ($invitation->getReceiver() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $invitation->setStatus(Invitation::STATUS_ACCEPTED);
        $group = $invitation->getGroup();
        $group->addMember($this->getUser());

        $notification = new Notification();
        $notification->setUser($invitation->getSender());
        $notification->setContent($this->getUser()->getFirstName() . ' accepted your invitation to ' . $group->getCategory());

        $entityManager->persist($notification);
        $entityManager->flush();

        $this->addFlash('success', 'You joined the group!');
        return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()]);
    }

    #[Route('/invitations/{id}/refuse', name: 'app_groups_invitation_refuse', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function refuseInvitation(Invitation $invitation, EntityManagerInterface $entityManager): Response
    {
        if ($invitation->getReceiver() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $invitation->setStatus(Invitation::STATUS_REJECTED);
        
        $notification = new Notification();
        $notification->setUser($invitation->getSender());
        $notification->setContent($this->getUser()->getFirstName() . ' refused your invitation to ' . $invitation->getGroup()->getCategory());

        $entityManager->persist($notification);
        $entityManager->flush();

        $this->addFlash('info', 'Invitation refused.');
        return $this->redirectToRoute('app_groups_invitations');
    }

    #[Route('/{id}', name: 'app_groups_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function show(Group $group): Response
    {
        $user = $this->getUser();

        // Allow admins, the group creator, or members to view the group
        $isMember = $group->getMembers()->contains($user);
        if (!$this->isGranted('ROLE_ADMIN') && $group->getCreator() !== $user && !$isMember) {
            throw $this->createAccessDeniedException('You can only view your own groups.');
        }

        return $this->render('groups/show.html.twig', [
            'group' => $group,
            'messages' => $group->getMessages(),
        ]);
    }

    /**
     * Front Office: Edit a group (students can only edit their own groups)
     */
    #[Route('/{id}/edit', name: 'app_groups_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
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
    #[Route('/{id}', name: 'app_groups_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ETUDIANT')]
    public function delete(Request $request, Group $group, EntityManagerInterface $entityManager, ScoreService $scoreService): Response
    {
        $user = $this->getUser();

        // Ensure student can only delete their own groups
        if ($group->getCreator() !== $user) {
            throw $this->createAccessDeniedException('You can only delete your own groups.');
        }

        if ($this->isCsrfTokenValid('delete'.$group->getId(), $request->request->get('_token'))) {
            // Reset scores of members
            $scoreService->resetScores($group->getMembers());
            
            $entityManager->remove($group);
            $entityManager->flush();

            $this->addFlash('success', 'Group deleted successfully and member scores reset!');
        }

        return $this->redirectToRoute('app_groups_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Back Office: Delete a group (admins can delete any group)
     */
    #[Route('/admin/{id}', name: 'app_admin_groups_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDelete(Request $request, Group $group, EntityManagerInterface $entityManager, ScoreService $scoreService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$group->getId(), $request->request->get('_token'))) {
            // Reset scores of members
            $scoreService->resetScores($group->getMembers());

            $entityManager->remove($group);
            $entityManager->flush();

            $this->addFlash('success', 'Group deleted successfully and member scores reset!');
        }

        return $this->redirectToRoute('app_admin_groups_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/invite', name: 'app_groups_invite', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ETUDIANT')]
    public function inviteUser(Request $request, Group $group, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        if ($group->getCreator() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $email = $request->request->get('email');
        $userToInvite = $userRepository->findOneBy(['email' => $email]);

        if (!$userToInvite) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()]);
        }

        if ($group->getMembers()->contains($userToInvite) || $group->getCreator() === $userToInvite) {
            $this->addFlash('error', 'User is already a member.');
            return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()]);
        }

        $invitation = new Invitation();
        $invitation->setSender($this->getUser());
        $invitation->setReceiver($userToInvite);
        $invitation->setGroup($group);

        $notification = new Notification();
        $notification->setUser($userToInvite);
        $notification->setContent('You have been invited to join the group ' . $group->getCategory());
        $notification->setLink($this->generateUrl('app_groups_invitations'));

        $entityManager->persist($invitation);
        $entityManager->persist($notification);
        $entityManager->flush();

        $this->addFlash('success', 'Invitation sent!');
        return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()]);
    }

    #[Route('/{id}/remove-member/{userId}', name: 'app_groups_remove_member', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function removeMember(Group $group, int $userId, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        // Only creator can remove members
        if ($group->getCreator() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Only the group creator can remove members.');
        }

        $userToRemove = $userRepository->find($userId);
        if (!$userToRemove) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()]);
        }

        if ($group->getMembers()->contains($userToRemove)) {
            $group->removeMember($userToRemove);
            
            // Optional: Find and delete/update invitation if it exists
            // This is good for consistency
            $entityManager->flush();
            $this->addFlash('success', 'Member removed successfully.');
        }

        return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()]);
    }

    #[Route('/{id}/message', name: 'app_groups_send_message', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function sendMessage(Request $request, Group $group, EntityManagerInterface $entityManager): Response
    {
        if (!$group->getMembers()->contains($this->getUser()) && $group->getCreator() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not a member of this group.');
        }

        $content = $request->request->get('content');
        if (empty($content)) {
            $this->addFlash('error', 'Message cannot be empty.');
            return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()]);
        }

        $message = new \App\Entity\Message();
        $message->setSender($this->getUser());
        $message->setGroup($group);
        $message->setContent($content);

        $entityManager->persist($message);
        $entityManager->flush();

        return $this->redirectToRoute('app_groups_show', ['id' => $group->getId()]);
    }
}
