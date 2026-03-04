<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserActionRepository;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_user_profile', methods: ['GET'])]
    public function index(UserActionRepository $actionRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentYear = (int) date('Y');

        $heatmapData = $actionRepository->countActionsForLastYear($user);
        $recentActions = $actionRepository->findBy(['user' => $user], ['createdAt' => 'DESC'], 10);

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'heatmapData' => $heatmapData,
            'recentActions' => $recentActions,
        ]);
    }

    #[Route('/edit', name: 'app_user_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('profilePicture')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('avatars_directory'),
                        $newFilename
                    );
                    $user->setProfilePicture($newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash('error', 'Error uploading profile picture');
                }
            }

            $skillsString = $form->get('skills')->getData();
            if ($skillsString !== null) {
                $skillsArray = array_map('trim', explode(',', $skillsString));
                $user->setSkills(array_values(array_filter($skillsArray)));
            } else {
                $user->setSkills([]);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Profile updated successfully!');

            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('user/edit_profile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
