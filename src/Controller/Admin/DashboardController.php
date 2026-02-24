<?php

namespace App\Controller\Admin;

use App\Repository\ActivityRepository;
use App\Repository\CourseRepository;
use App\Repository\EventRepository;
use App\Repository\GroupRepository;
use App\Repository\TaskRepository;
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
    public function index(
        UserRepository $userRepository,
        CourseRepository $courseRepository,
        ActivityRepository $activityRepository,
        TaskRepository $taskRepository,
        GroupRepository $groupRepository,
        EventRepository $eventRepository,
    ): Response {
        // Users
        $totalUsers = $userRepository->count([]);

        // Mocking role counts since countByRole might not exist yet in Repo
        // Optimized: In a real app, use a custom repository method for this.
        $allUsers = $userRepository->findAll();
        $students = 0;
        $admins = 0;
        foreach ($allUsers as $u) {
            if (in_array('ROLE_ETUDIANT', $u->getRoles())) {
                $students++;
            }
            if (in_array('ROLE_ADMIN', $u->getRoles())) {
                $admins++;
            }
        }

        $recentUsers = $userRepository->findBy([], ['createdAt' => 'DESC'], 5);

        // Courses & learning data
        $totalCourses = $courseRepository->count([]);
        $totalActivities = $activityRepository->count([]);

        // Tasks (done vs not done)
        // NOTE: adjust the status values below to match what you use in forms (e.g. 'Completed', 'En cours', etc.).
        $totalTasks = $taskRepository->count([]);
        $completedTasks = $taskRepository->count(['status' => 'COMPLETED']);
        $pendingTasks = $taskRepository->count(['status' => 'PENDING']);

        // Groups & events
        $totalGroups = $groupRepository->count([]);
        $totalEvents = $eventRepository->count([]);

        // All courses for management overview
        $allCourses = $courseRepository->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'students' => $students,
            'admins' => $admins,
            'recentUsers' => $recentUsers,
            'totalCourses' => $totalCourses,
            'totalActivities' => $totalActivities,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'pendingTasks' => $pendingTasks,
            'totalGroups' => $totalGroups,
            'totalEvents' => $totalEvents,
            'allCourses' => $allCourses,
        ]);
    }
}
