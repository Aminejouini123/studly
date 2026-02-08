<?php

namespace App\Controller\Auth;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AuthController extends AbstractController
{
    #[Route('/auth', name: 'app_auth')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, \App\Repository\UserRepository $userRepository): Response
    {
        $email = $request->request->get('email');

        // Check if user already exists
        if ($userRepository->findOneBy(['email' => $email])) {
            $this->addFlash('error', 'This email is already registered.');
            return $this->redirectToRoute('app_auth');
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($request->request->get('firstName'));
        $user->setLastName($request->request->get('lastName'));

        // Handle optional Date Of Birth
        $dob = $request->request->get('dateOfBirth');
        if ($dob) {
            $user->setDateOfBirth(new \DateTime($dob));
        }

        $user->setPhoneNumber($request->request->get('phoneNumber') ?? null);
        $user->setAddress($request->request->get('address') ?? null);

        $user->setRoles(['ROLE_ETUDIANT']); // Default role
        $user->setStatut('Active');

        // encode the plain password
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $request->request->get('password')
            )
        );

        $entityManager->persist($user);
        $entityManager->flush();

        // Redirect to login (auth page)
        return $this->redirectToRoute('app_auth');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
