<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class VerifyEmailController extends AbstractController
{
    #[Route('/verify-email', name: 'app_verify_email')]
    public function verifyEmail(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('User not found');
        }

        if ($user->isVerified()) {
            return $this->redirectToRoute('app_front');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $code = $request->request->get('code');

            if ($code === $user->getVerificationCode()) {
                $user->setIsVerified(true);
                $user->setVerificationCode(null);
                $user->setStatut('Active'); // Activate user
                $entityManager->flush();

                return $this->redirectToRoute('app_front');
            } else {
                $error = 'Invalid verification code.';
            }
        }

        return $this->render('auth/verify_email.html.twig', [
            'error' => $error,
        ]);
    }
}
