<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Exam;
use App\Form\ExamType;
use App\Repository\ExamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\UserActionLogger;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ExamenController extends AbstractController
{
    #[Route('/course/{id}/exams', name: 'app_course_exams', methods: ['GET'])]
    public function index(Course $course): Response
    {
        // specific course exams
        $user = $this->getUser();

        // Security check: Ensure user owns the course OR is admin
        if ($course->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to view exams for this course.');
        }

        // Exams are already linked to the course, we can use $course->getExams() 
        // OR fetch via repository if we want specific ordering/filtering not in the collection
        // Let's use the collection filter/sort in twig or repository if needed. 
        // For simple ordering by date, let's use a sorted collection or repository method.
        // Using repository for cleaner ordering:

        $exams = $course->getExams()->toArray();
        usort($exams, fn($a, $b) => $a->getDate() <=> $b->getDate());

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->render('examen/backExamen.html.twig', [
                'course' => $course,
                'exams' => $exams,
            ]);
        }

        return $this->render('examen/frontExamen.html.twig', [
            'course' => $course,
            'exams' => $exams,
        ]);
    }

    #[Route('/course/{id}/exam/new', name: 'app_course_exam_new', methods: ['GET', 'POST'])]
    public function new(Course $course, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, UserActionLogger $actionLogger): Response
    {
        $user = $this->getUser();
        if ($course->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to add exams to this course.');
        }

        $exam = new Exam();
        $exam->setCourse($course);
        $exam->setDate(new \DateTime()); // Default to current date and time

        $form = $this->createForm(ExamType::class, $exam);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload if present (assuming file field ex   ists in entity/form)
            /** @var UploadedFile|null $file */
            $file = $form->get('file')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/exams',
                        $newFilename
                    );
                    $exam->setFile($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading file');
                }
            }

            $entityManager->persist($exam);

            // Log action
            $actionLogger->log($this->getUser(), 'exam_created', 'Created a new exam: ' . $exam->getTitle() . ' for course ' . $course->getName(), $exam);

            $entityManager->flush();

            $this->addFlash('success', 'Exam successfully added!');

            return $this->redirectToRoute('app_course_exams', ['id' => $course->getId()]);
        }

        return $this->render('examen/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/admin/course/{id}/exam/new', name: 'app_admin_course_exam_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function newAdmin(Course $course, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, UserActionLogger $actionLogger): Response
    {
        $exam = new Exam();
        $exam->setCourse($course);
        $exam->setDate(new \DateTime()); // Default to current date and time

        $form = $this->createForm(ExamType::class, $exam);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $file */
            $file = $form->get('file')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/exams',
                        $newFilename
                    );
                    $exam->setFile($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading file');
                }
            }

            $entityManager->persist($exam);

            // Log action
            $actionLogger->log($this->getUser(), 'exam_created', 'Created a new exam (Admin): ' . $exam->getTitle() . ' for course ' . $course->getName(), $exam);

            $entityManager->flush();

            $this->addFlash('success', 'Exam successfully added!');

            return $this->redirectToRoute('app_course_exams', ['id' => $course->getId()]);
        }

        return $this->render('examen/backNew.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }
    #[Route('/exam/{id}/edit', name: 'app_exam_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Exam $exam, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $course = $exam->getCourse();
        // Security check
        if ($course->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ExamType::class, $exam);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $file */
            $file = $form->get('file')->getData();
            if ($file) {
                // Delete old file if exists? Optional improvement
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/exams',
                        $newFilename
                    );
                    $exam->setFile($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading file');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Exam successfully updated!');

            return $this->redirectToRoute('app_course_exams', ['id' => $course->getId()]);
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->render('examen/backEdit.html.twig', [
                'course' => $course,
                'exam' => $exam,
                'form' => $form,
            ]);
        }

        return $this->render('examen/edit.html.twig', [
            'course' => $course,
            'exam' => $exam,
            'form' => $form,
        ]);
    }

    #[Route('/admin/examen/{id}/edit', name: 'app_admin_exam_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editAdmin(Request $request, Exam $exam, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $course = $exam->getCourse();

        $form = $this->createForm(ExamType::class, $exam);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $file */
            $file = $form->get('file')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/exams',
                        $newFilename
                    );
                    $exam->setFile($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading file');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Exam successfully updated!');

            return $this->redirectToRoute('app_course_exams', ['id' => $course->getId()]);
        }

        return $this->render('examen/backEdit.html.twig', [
            'course' => $course,
            'exam' => $exam,
            'form' => $form,
        ]);
    }

    #[Route('/exam/{id}', name: 'app_exam_show', methods: ['GET'])]
    public function show(Exam $exam): Response
    {
        $course = $exam->getCourse();
        // Security check
        if ($course->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to view this exam.');
        }

        return $this->render('examen/show.html.twig', [
            'exam' => $exam,
        ]);
    }

    #[Route('/exam/{id}/delete', name: 'app_exam_delete', methods: ['POST'])]
    public function delete(Request $request, Exam $exam, EntityManagerInterface $entityManager): Response
    {
        $course = $exam->getCourse();
        if ($course->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $exam->getId(), $request->request->get('_token'))) {
            $entityManager->remove($exam);
            $entityManager->flush();
            $this->addFlash('success', 'Exam successfully deleted!');
        }

        return $this->redirectToRoute('app_course_exams', ['id' => $course->getId()]);
    }
}
