<?php

declare(strict_types=1);


namespace App\Controller\Api;

use App\Entity\Invitation;
use App\Repository\GroupRepository;
use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ApiInvitationController extends AbstractController
{
    /**
     * POST /api/groups/{id}/invite
     * Invite un utilisateur dans un groupe.
     * Body JSON attendu : { "userId": 42 }
     */
    #[Route('/api/groups/{id}/invite', name: 'api_groups_invite', methods: ['POST'])]
    public function invite(
        int $id,
        Request $request,
        GroupRepository $groupRepository,
        UserRepository $userRepository,
        InvitationRepository $invitationRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $group = $groupRepository->find($id);

        if (!$group) {
            return $this->json(['error' => 'Groupe introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $body = json_decode($request->getContent(), true);

        if (empty($body['userId'])) {
            return $this->json(['error' => 'Le champ "userId" est obligatoire.'], Response::HTTP_BAD_REQUEST);
        }

        $receiver = $userRepository->find($body['userId']);

        if (!$receiver) {
            return $this->json(['error' => 'Utilisateur introuvable.'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier qu'une invitation PENDING n'existe pas déjà
        $existing = $invitationRepository->findOneBy([
            'group'    => $group,
            'receiver' => $receiver,
            'status'   => Invitation::STATUS_PENDING,
        ]);

        if ($existing) {
            return $this->json(['error' => 'Une invitation est déjà en attente pour cet utilisateur.'], Response::HTTP_CONFLICT);
        }

        $invitation = new Invitation();
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('User not found');
        }
        $invitation->setSender($user);
        $invitation->setReceiver($receiver);
        $invitation->setGroup($group);
        $invitation->setStatus(Invitation::STATUS_PENDING);

        $em->persist($invitation);
        $em->flush();

        return $this->json([
            'message'      => 'Invitation envoyée avec succès.',
            'invitationId' => $invitation->getId(),
        ], Response::HTTP_CREATED);
    }

    /**
     * POST /api/invitations/{id}/accept
     * Accepte une invitation et ajoute l'utilisateur comme membre du groupe.
     */
    #[Route('/api/invitations/{id}/accept', name: 'api_invitations_accept', methods: ['POST'])]
    public function accept(
        int $id,
        InvitationRepository $invitationRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $invitation = $invitationRepository->find($id);

        if (!$invitation) {
            return $this->json(['error' => 'Invitation introuvable.'], Response::HTTP_NOT_FOUND);
        }

        if ($invitation->getStatus() !== Invitation::STATUS_PENDING) {
            return $this->json(['error' => 'Cette invitation a déjà été traitée.'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier que c'est bien le destinataire qui accepte
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if ($user === null || ($invitation->getReceiver() !== null && $invitation->getReceiver()->getId() !== $user->getId())) {
            return $this->json(['error' => 'Vous n\'êtes pas autorisé à accepter cette invitation.'], Response::HTTP_FORBIDDEN);
        }

        $invitation->setStatus(Invitation::STATUS_ACCEPTED);
        $invitation->getGroup()->addMember($invitation->getReceiver());

        $em->flush();

        return $this->json(['message' => 'Invitation acceptée. Vous êtes maintenant membre du groupe.'], Response::HTTP_OK);
    }

    /**
     * POST /api/invitations/{id}/refuse
     * Refuse une invitation.
     */
    #[Route('/api/invitations/{id}/refuse', name: 'api_invitations_refuse', methods: ['POST'])]
    public function refuse(
        int $id,
        InvitationRepository $invitationRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $invitation = $invitationRepository->find($id);

        if (!$invitation) {
            return $this->json(['error' => 'Invitation introuvable.'], Response::HTTP_NOT_FOUND);
        }

        if ($invitation->getStatus() !== Invitation::STATUS_PENDING) {
            return $this->json(['error' => 'Cette invitation a déjà été traitée.'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier que c'est bien le destinataire qui refuse
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if ($user === null || ($invitation->getReceiver() !== null && $invitation->getReceiver()->getId() !== $user->getId())) {
            return $this->json(['error' => 'Vous n\'êtes pas autorisé à refuser cette invitation.'], Response::HTTP_FORBIDDEN);
        }

        $invitation->setStatus(Invitation::STATUS_REJECTED);

        $em->flush();

        return $this->json(['message' => 'Invitation refusée.'], Response::HTTP_OK);
    }
}
