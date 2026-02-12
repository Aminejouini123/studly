<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function index(UserRepository $userRepository): Response
    {
        // Real gathered stats
        $totalUsers = $userRepository->count([]);

        // Mocking role counts since countByRole might not exist yet in Repo
        // Optimized: In a real app, use a custom repository method for this.
        $allUsers = $userRepository->findAll();
        $students = 0;
        $admins = 0;
        foreach ($allUsers as $u) {
            if (in_array('ROLE_ETUDIANT', $u->getRoles()))
                $students++;
            if (in_array('ROLE_ADMIN', $u->getRoles()))
                $admins++;
        }

        $recentUsers = $userRepository->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'students' => $students,
            'admins' => $admins,
            'recentUsers' => $recentUsers,
            'revenueData' => [1200, 1900, 3000, 5000, 2000, 3000], // Example data
        ]);
    }
}
