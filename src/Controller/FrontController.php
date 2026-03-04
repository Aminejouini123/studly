<?php

namespace App\Controller;

use App\Repository\CourseRepository;
use App\Repository\EventRepository;
use App\Entity\User;
use App\Repository\ExamRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ETUDIANT')]
final class FrontController extends AbstractController
{
    #[Route('/front', name: 'app_front')]
    public function index(
        CourseRepository $courseRepository,
        EventRepository $eventRepository,
        ExamRepository $examRepository,
        TaskRepository $taskRepository,
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();

        $userCoursesCount = 0;
        $userEventsCount = 0;
        $completedEventsCount = 0;
        $upcomingExamsCount = 0;
        $pendingTasksCount = 0;
        $topCourse = null;
        $upcomingEvents = [];
        $recentCourses = [];

        if ($user !== null) {
            $userCoursesCount = $courseRepository->countByUser($user);
            $userEventsCount = $eventRepository->countByUser($user);
            $completedEventsCount = $eventRepository->countCompletedByUser($user);
            $upcomingExamsCount = $examRepository->countUpcomingByUser($user);
            $pendingTasksCount = $taskRepository->countPendingByUser($user);
            $topCourse = $courseRepository->findTopCourseForUser($user);
            $upcomingEvents = $eventRepository->findUpcomingByUser($user, 3);
            $recentCourses = $courseRepository->findRecentByUser($user, 4);
        }

        return $this->render('front/front.html.twig', [
            'controller_name' => 'FrontController',
            'userCoursesCount' => $userCoursesCount,
            'userEventsCount' => $userEventsCount,
            'completedEventsCount' => $completedEventsCount,
            'upcomingExamsCount' => $upcomingExamsCount,
            'pendingTasksCount' => $pendingTasksCount,
            'topCourse' => $topCourse,
            'upcomingEvents' => $upcomingEvents,
            'recentCourses' => $recentCourses,
        ]);
    }
}
