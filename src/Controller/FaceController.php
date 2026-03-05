<?php

namespace App\Controller;

use App\Service\FaceRecognitionService;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/face')]
class FaceController extends AbstractController
{
    #[Route('/register', name: 'app_face_register', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function register(Request $request, FaceRecognitionService $faceService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['descriptor']) || !is_array($data['descriptor']) || count($data['descriptor']) !== 128) {
            return new JsonResponse(['error' => 'Invalid descriptor format. Must be an array of 128 floats.'], 400);
        }

        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('User not found');
        }

        try {
            $result = $faceService->register($user->getId(), $data['descriptor']);
            return new JsonResponse($result);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/login', name: 'app_face_login', methods: ['POST'])]
    public function login(
        Request $request,
        FaceRecognitionService $faceService,
        EntityManagerInterface $entityManager,
        Security $security
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['descriptor']) || !is_array($data['descriptor']) || count($data['descriptor']) !== 128) {
            return new JsonResponse(['error' => 'Invalid descriptor format.'], 400);
        }

        $result = $faceService->login($data['descriptor']);

        if ($result && isset($result['user_id'])) {
            $user = $entityManager->getRepository(User::class)->find($result['user_id']);

            if (!$user) {
                return new JsonResponse(['error' => 'User not found.'], 404);
            }

            // Log the user in programmatically via Security service
            $security->login($user, 'form_login', 'main');

            // Determine redirect URL
            $redirect = in_array('ROLE_ADMIN', $user->getRoles()) ? $this->generateUrl('app_admin_dashboard') : $this->generateUrl('app_front');

            return new JsonResponse([
                'status' => 'success',
                'confidence' => $result['confidence'],
                'redirect' => $redirect
            ]);
        }

        return new JsonResponse(['error' => 'Face not recognized.'], 401);
    }
}
