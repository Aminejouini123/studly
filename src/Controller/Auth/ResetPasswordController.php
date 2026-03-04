<?php

namespace App\Controller\Auth;

use App\Service\PasswordResetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function __invoke(
        string $token,
        Request $request,
        PasswordResetService $resetService,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        $reset = $resetService->findValidToken($token);

        if (!$reset) {
            $this->addFlash('error', 'Reset link is invalid or has expired.');
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $password = (string) $request->request->get('password', '');
            $confirm = (string) $request->request->get('password_confirm', '');

            if ($password === '' || strlen($password) < 8) {
                $this->addFlash('error', 'Password must be at least 8 characters.');
                return $this->redirectToRoute('app_reset_password', ['token' => $token]);
            }

            if ($password !== $confirm) {
                $this->addFlash('error', 'Passwords do not match.');
                return $this->redirectToRoute('app_reset_password', ['token' => $token]);
            }

            $user = $reset->getUser();
            if (!$user) {
                $this->addFlash('error', 'User associated with this token not found.');
                return $this->redirectToRoute('app_forgot_password');
            }

            $hashed = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashed);

            $em->flush();

            $resetService->invalidate($reset);

            $this->addFlash('success', 'Your password has been reset. You can now log in.');
            return $this->redirectToRoute('app_auth');
        }

        return $this->render('auth/reset_password.html.twig', [
            'token' => $token,
        ]);
    }
}

