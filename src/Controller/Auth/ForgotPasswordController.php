<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Service\PasswordResetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function __invoke(
        Request $request,
        EntityManagerInterface $em,
        PasswordResetService $resetService,
        MailerInterface $mailer
    ): Response {
        if ($request->isMethod('POST')) {
            $email = (string) $request->request->get('email', '');

            /** @var User|null $user */
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $reset = $resetService->createToken($user);

                $resetUrl = $this->generateUrl(
                    'app_reset_password',
                    ['token' => $reset->getToken()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $message = (new TemplatedEmail())
                    ->from($this->getParameter('mailer.from_address'))
                    ->to($user->getEmail())
                    ->subject('Reset your password')
                    ->htmlTemplate('emails/reset_password.html.twig')
                    ->context([
                        'user' => $user,
                        'resetUrl' => $resetUrl,
                    ]);

                $mailer->send($message);
            }

            $this->addFlash('success', 'If an account exists for this email, a reset link has been sent.');
            return $this->redirectToRoute('app_forgot_password');
        }

        return $this->render('auth/forgot_password.html.twig');
    }
}

