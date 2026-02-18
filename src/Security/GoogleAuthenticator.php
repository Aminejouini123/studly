<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $entityManager;
    private RouterInterface $router;
    private MailerInterface $mailer;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router, MailerInterface $mailer)
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->mailer = $mailer;
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);

                $email = $googleUser->getEmail();

                // 1) have they logged in with Google before?
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['googleId' => $googleUser->getId()]);

                if ($existingUser) {
                    return $existingUser;
                }

                // 2) do we have a matching user by email?
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if ($user) {
                    $user->setGoogleId($googleUser->getId());
                    $this->entityManager->flush();
                    return $user;
                }

                // 3) New User
                $user = new User();
                $user->setEmail($email);
                $user->setGoogleId($googleUser->getId());
                $user->setFirstName($googleUser->getFirstName());
                $user->setLastName($googleUser->getLastName());
                // Generate a random password since one is required
                $user->setPassword(bin2hex(random_bytes(20)));
                $user->setIsVerified(false);
                $user->setStatut('Pending');

                // Generate 6-digit code
                $code = (string) random_int(100000, 999999);
                $user->setVerificationCode($code);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                // Send email
                $emailMessage = (new Email())
                    ->from('no-reply@studly.com')
                    ->to($user->getEmail())
                    ->subject('Your Verification Code')
                    ->text('Your verification code is: ' . $code);

                try {
                    $this->mailer->send($emailMessage);
                } catch (\Exception $e) {
                    dd($e->getMessage());
                    // Log error but allow creation to proceed? Or fail?
                    // For now proceeding.
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Get the User object
        $user = $token->getUser();

        if ($user instanceof User) {
            if (!$user->isVerified()) {
                return new RedirectResponse($this->router->generate('app_verify_email'));
            }

            // Redirect based on role
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return new RedirectResponse($this->router->generate('admin'));
            }
        }

        return new RedirectResponse($this->router->generate('app_front'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }
}
