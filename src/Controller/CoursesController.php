<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CoursesController extends AbstractController
{
    #[Route('/courses', name: 'app_courses')]
    public function index(CourseRepository $courseRepository): Response
    {
        // Filter courses by the current logged-in user
        // Ensure the user is logged in (handled by firewall usually, but strict typing helps)
        $user = $this->getUser();
        
        $courses = $user ? $courseRepository->findBy(['user' => $user]) : [];
        
        return $this->render('courses/frontCourses.html.twig', [
            'courses' => $courses,
        ]);
    }

    #[Route('/course/new', name: 'app_course_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Assign the current user to the course
            $course->setUser($this->getUser());

            // handle uploaded file
            /** @var UploadedFile|null $uploadedFile */
            $uploadedFile = $form->get('courseFile')->getData();
            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

                $targetDir = $this->getParameter('kernel.project_dir').'/public/uploads/courses';
                try {
                    $uploadedFile->move($targetDir, $newFilename);
                    $course->setCourseFile($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Impossible d\'enregistrer le fichier.');
                }
            }

            $course->setCreatedAt(new \DateTime());
            $entityManager->persist($course);
            $entityManager->flush();

            $this->addFlash('success', 'Le cours a été ajouté avec succès!');
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
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

                $targetDir = $this->getParameter('kernel.project_dir').'/public/uploads/courses';
                try {
                    $uploadedFile->move($targetDir, $newFilename);
                    $course->setCourseFile($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Impossible d\'enregistrer le fichier.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le cours a été modifié avec succès!');
            
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

        $this->addFlash('success', 'Le cours a été supprimé avec succès!');
        
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
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

                $targetDir = $this->getParameter('kernel.project_dir').'/public/uploads/courses';
                try {
                    $uploadedFile->move($targetDir, $newFilename);
                    $course->setCourseFile($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Impossible d\'enregistrer le fichier.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le cours a été modifié avec succès!');
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
