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
    public function index(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $session = $request->getSession();

        $registrationData = $session->get('registration_data', []);
        $registrationErrors = $session->get('registration_errors', [
            'global' => [],
            'fields' => [],
        ]);

        // Optionally clear errors after displaying once
        $session->remove('registration_errors');

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'registrationData' => $registrationData,
            'registrationErrors' => $registrationErrors,
        ]);
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        \App\Repository\UserRepository $userRepository,
        \Symfony\Component\Validator\Validator\ValidatorInterface $validator
    ): Response {
        $user = new User();

        // Set all user data from the form
        $user->setEmail($request->request->get('email'));
        $user->setFirstName($request->request->get('firstName'));
        $user->setLastName($request->request->get('lastName'));

        // Set plain password for validation
        $plainPassword = $request->request->get('password');
        $user->setPlainPassword($plainPassword);

        // Handle Date Of Birth
        $dob = $request->request->get('dateOfBirth');
        if ($dob) {
            try {
                $user->setDateOfBirth(new \DateTime($dob));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Invalid date format.');
                return $this->redirectToRoute('app_auth');
            }
        }

        $user->setPhoneNumber($request->request->get('phoneNumber'));
        $user->setAddress($request->request->get('address'));
        $user->setRoles(['ROLE_ETUDIANT']); // Default role
        $user->setStatut('Active');

        // Validate the user entity with 'create' validation group
        $errors = $validator->validate($user, null, ['Default', 'create']);

        if (count($errors) > 0) {
            $fieldErrors = [];
            $globalErrors = [];

            foreach ($errors as $error) {
                $path = $error->getPropertyPath();

                // Map internal property names to input names when needed
                if ($path === 'plainPassword') {
                    $path = 'password';
                }

                if ($path) {
                    $fieldErrors[$path][] = $error->getMessage();
                } else {
                    $globalErrors[] = $error->getMessage();
                }
            }

            // Store form data in session to repopulate the form
            $request->getSession()->set('registration_data', [
                'firstName' => $request->request->get('firstName'),
                'lastName' => $request->request->get('lastName'),
                'email' => $request->request->get('email'),
                'dateOfBirth' => $request->request->get('dateOfBirth'),
                'phoneNumber' => $request->request->get('phoneNumber'),
                'address' => $request->request->get('address'),
            ]);

            // Store structured errors in session so Twig can display them on the Sign Up form
            $request->getSession()->set('registration_errors', [
                'global' => $globalErrors,
                'fields' => $fieldErrors,
            ]);

            return $this->redirectToRoute('app_auth');
        }

        // Hash the password
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $plainPassword
            )
        );

        // Clear plain password before persisting
        $user->eraseCredentials();

        $entityManager->persist($user);
        $entityManager->flush();

        // Clear registration data from session
        $request->getSession()->remove('registration_data');

        // Add success message
        $this->addFlash('success', 'Registration successful! Please sign in.');

        // Redirect to login (auth page)
        return $this->redirectToRoute('app_auth');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
