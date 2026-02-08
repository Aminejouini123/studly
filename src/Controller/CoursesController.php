<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CoursesController extends AbstractController
{
    #[Route('/courses', name: 'app_courses')]
    public function index(): Response
    {
        return $this->render('courses/frontCourses.html.twig', [
            'controller_name' => 'CoursesController',
        ]);
    }

    #[Route('/admin/courses', name: 'app_admin_courses')]
    public function adminIndex(): Response
    {
        return $this->render('courses/backCourses.html.twig', [
            'controller_name' => 'CoursesController',
        ]);
    }
}
