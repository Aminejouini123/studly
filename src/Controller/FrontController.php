<?php

declare(strict_types=1);


namespace App\Controller;

use App\Repository\CourseRepository;
use App\Repository\EventRepository;
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
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('User not found');
        }

        $userCoursesCount = $courseRepository->countByUser($user);
        $userEventsCount = $eventRepository->countByUser($user);
        $topCourse = $courseRepository->findTopCourseForUser($user);

        return $this->render('front/front.html.twig', [
            'controller_name' => 'FrontController',
            'userCoursesCount' => $userCoursesCount,
            'userEventsCount' => $userEventsCount,
            'topCourse' => $topCourse,
        ]);
    }
}
