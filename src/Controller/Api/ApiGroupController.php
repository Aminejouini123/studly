<?php

namespace App\Controller\Api;

use App\Entity\Group;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/groups', name: 'api_groups_')]
#[IsGranted('ROLE_USER')]
class ApiGroupController extends AbstractController
{
    /**
     * GET /api/groups
     * Retourne la liste de tous les groupes.
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(GroupRepository $groupRepository): JsonResponse
    {
        $groups = $groupRepository->findAll();

        $data = array_map(fn(Group $g) => $this->serializeGroup($g), $groups);

        return $this->json($data, Response::HTTP_OK);
    }

    /**
     * POST /api/groups
     * Crée un nouveau groupe.
     * Body JSON attendu : { "category": "Dev", "capacity": 10, "groupPhoto": "https://..." }
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $body = json_decode($request->getContent(), true);

        if (empty($body['category'])) {
            return $this->json(['error' => 'Le champ "category" est obligatoire.'], Response::HTTP_BAD_REQUEST);
        }

        $group = new Group();
        $group->setCategory($body['category']);
        $group->setCapacity($body['capacity'] ?? 1);
        $group->setGroupPhoto($body['groupPhoto'] ?? null);
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('User not found');
        }
        $group->setCreator($user);

        $em->persist($group);
        $em->flush();

        return $this->json($this->serializeGroup($group), Response::HTTP_CREATED);
    }

    /**
     * GET /api/groups/{id}
     * Retourne le détail d'un groupe (avec membres et projets).
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, GroupRepository $groupRepository): JsonResponse
    {
        $group = $groupRepository->find($id);

        if (!$group) {
            return $this->json(['error' => 'Groupe introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializeGroup($group);

        // Membres
        $data['members'] = array_map(fn($u) => [
            'id'    => $u->getId(),
            'email' => $u->getEmail(),
            'name'  => $u->getFirstName() . ' ' . $u->getLastName(),
        ], $group->getMembers()->toArray());

        // Projets
        $data['projects'] = array_map(fn($p) => [
            'id'     => $p->getId(),
            'title'  => $p->getTitle(),
            'status' => $p->getStatus(),
        ], $group->getProjects()->toArray());

        return $this->json($data, Response::HTTP_OK);
    }

    /**
     * DELETE /api/groups/{id}
     * Supprime un groupe.
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, GroupRepository $groupRepository, EntityManagerInterface $em): JsonResponse
    {
        $group = $groupRepository->find($id);

        if (!$group) {
            return $this->json(['error' => 'Groupe introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($group);
        $em->flush();

        return $this->json(['message' => 'Groupe supprimé avec succès.'], Response::HTTP_OK);
    }

    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    private function serializeGroup(Group $group): array
    {
        return [
            'id'         => $group->getId(),
            'category'   => $group->getCategory(),
            'capacity'   => $group->getCapacity(),
            'groupPhoto' => $group->getGroupPhoto(),
            'createdAt'  => $group->getCreatedAt()?->format('Y-m-d H:i:s'),
            'creator'    => $group->getCreator() ? [
                'id'    => $group->getCreator()->getId(),
                'email' => $group->getCreator()->getEmail(),
            ] : null,
        ];
    }
}
