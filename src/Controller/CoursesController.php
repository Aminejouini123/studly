<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\User;
use App\Form\CourseType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\UserActionLogger;
use Symfony\Component\Routing\Attribute\Route;

final class CoursesController extends AbstractController
{
    #[Route('/courses', name: 'app_courses')]
    public function index(CourseRepository $courseRepository): Response
    {
        $user = $this->getUser();

        $courses = $user instanceof User
            ? $courseRepository->findBy(['user' => $user])
            : [];
        return $this->render('courses/frontCourses.html.twig', [
            'courses' => $courses,
        ]);
    }

    #[Route('/course/new', name: 'app_course_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, UserActionLogger $actionLogger): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw $this->createAccessDeniedException('You must be logged in to create a course.');
            }

            // Assign the current user to the course
            $course->setUser($user);

            // handle uploaded file
            /** @var UploadedFile|null $uploadedFile */
            $uploadedFile = $form->get('courseFile')->getData();
            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

                $targetDir = $this->getParameter('kernel.project_dir') . '/public/uploads/courses';
                try {
                    $uploadedFile->move($targetDir, $newFilename);
                    $course->setCourseFile($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Unable to save the file.');
                }
            }

            $course->setCreatedAt(new \DateTime());
            $entityManager->persist($course);

            // Log the action
            $actionLogger->log($this->getUser(), 'course_created', 'Created a new course: ' . $course->getName(), $course);

            $entityManager->flush();

            $this->addFlash('success', 'The course has been successfully added!');
            return $this->redirectToRoute('app_courses');
        }

        return $this->render('courses/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/course/{id}/edit', name: 'app_course_edit', methods: ['GET', 'POST'])]
    public function edit(Course $course, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Check if the current user is the owner of the course OR is an admin
        if ($course->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to edit this course.');
        }

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // handle uploaded file replacement
            /** @var UploadedFile|null $uploadedFile */
            $uploadedFile = $form->get('courseFile')->getData();
            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

                $targetDir = $this->getParameter('kernel.project_dir') . '/public/uploads/courses';
                try {
                    $uploadedFile->move($targetDir, $newFilename);
                    $course->setCourseFile($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Unable to save the file.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'The course has been successfully updated!');

            // Redirect based on role/referer might be better, but for now:
            if ($this->isGranted('ROLE_ADMIN') && strpos($request->headers->get('referer'), '/admin') !== false) {
                return $this->redirectToRoute('app_admin_courses');
            }
            return $this->redirectToRoute('app_courses');
        }

        return $this->render('courses/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/course/{id}', name: 'app_course_show', methods: ['GET'])]
    public function show(Course $course): Response
    {
        // Check if the current user is the owner of the course OR admin
        if ($course->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to view this course.');
        }

        return $this->render('courses/show.html.twig', [
            'course' => $course,
        ]);
    }

    #[Route('/course/{id}/delete', name: 'app_course_delete', methods: ['POST'])]
    public function delete(Course $course, EntityManagerInterface $entityManager): Response
    {
        // Check if the current user is the owner of the course OR admin
        if ($course->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to delete this course.');
        }

        $entityManager->remove($course);
        $entityManager->flush();

        $this->addFlash('success', 'The course has been successfully deleted!');

        // rudimentary check if we came from admin
        // better way is to pass a 'from' query param or check referer
        // For now, let's just redirect to admin if admin
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin_courses');
        }

        return $this->redirectToRoute('app_courses');
    }



    #[Route('/admin/course/{id}', name: 'app_admin_course_show', methods: ['GET'])]
    public function adminShow(Course $course): Response
    {
        return $this->render('courses/backShow.html.twig', [
            'course' => $course,
        ]);
    }

    #[Route('/admin/course/{id}/edit', name: 'app_admin_course_edit', methods: ['GET', 'POST'])]
    public function adminEdit(Course $course, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // handle uploaded file replacement
            /** @var UploadedFile|null $uploadedFile */
            $uploadedFile = $form->get('courseFile')->getData();
            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

                $targetDir = $this->getParameter('kernel.project_dir') . '/public/uploads/courses';
                try {
                    $uploadedFile->move($targetDir, $newFilename);
                    $course->setCourseFile($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Unable to save the file.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'The course has been successfully updated!');
            return $this->redirectToRoute('app_admin_courses');
        }

        return $this->render('courses/backEdit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/admin/courses', name: 'app_admin_courses')]
    public function adminIndex(CourseRepository $courseRepository): Response
    {
        return $this->render('courses/backCourses.html.twig', [
            'courses' => $courseRepository->findAll(),
        ]);
    }


}
