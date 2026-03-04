<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Course;
use App\Form\ActivityType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ActivityController extends AbstractController
{
    #[Route('/course/{id}/activities', name: 'app_course_activities', methods: ['GET'])]
    public function index(int $id, \App\Repository\CourseRepository $courseRepository): Response
    {
        $course = $courseRepository->find($id);

        if (!$course) {
            $this->addFlash('error', 'This course no longer exists.');
            return $this->redirectToRoute('app_courses');
        }

        $user = $this->getUser();
        if ($course->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to view activities for this course.');
        }

        $activities = $course->getActivities();

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->render('activity/backActivity.html.twig', [
                'course' => $course,
                'activities' => $activities,
            ]);
        }

        return $this->render('activity/frontActivity.html.twig', [
            'course' => $course,
            'activities' => $activities,
        ]);
    }

    #[Route('/course/{id}/activity/new', name: 'app_course_activity_new', methods: ['GET', 'POST'])]
    public function new(Course $course, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        if ($course->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to add activities to this course.');
        }

        $activity = new Activity();
        $activity->setCourse($course);

        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $file */
            $file = $form->get('file')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/activities',
                        $newFilename
                    );
                    $activity->setFile($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading file');
                }
            }

            $entityManager->persist($activity);
            $entityManager->flush();

            return $this->redirectToRoute('app_course_activities', ['id' => $course->getId()]);
        }

        return $this->render('activity/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/admin/course/{id}/activity/new', name: 'app_admin_course_activity_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function newAdmin(Course $course, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $activity = new Activity();
        $activity->setCourse($course);

        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $file */
            $file = $form->get('file')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/activities',
                        $newFilename
                    );
                    $activity->setFile($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading file');
                }
            }

            $entityManager->persist($activity);
            $entityManager->flush();

            return $this->redirectToRoute('app_course_activities', ['id' => $course->getId()]);
        }

        return $this->render('activity/backNew.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/activity/{id}', name: 'app_activity_show', methods: ['GET'])]
    public function show(Activity $activity): Response
    {
        $user = $this->getUser();
        if ($activity->getCourse()->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to view this activity.');
        }

        return $this->render('activity/show.html.twig', [
            'activity' => $activity,
        ]);
    }

    #[Route('/activity/{id}/edit', name: 'app_activity_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Activity $activity, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        if ($activity->getCourse()->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to edit this activity.');
        }

        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             /** @var UploadedFile|null $file */
            $file = $form->get('file')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/activities',
                        $newFilename
                    );
                    $activity->setFile($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading file');
                }
            }
            
            $entityManager->flush();

            return $this->redirectToRoute('app_course_activities', ['id' => $activity->getCourse()->getId()]);
        }

        return $this->render('activity/edit.html.twig', [
            'activity' => $activity,
            'form' => $form,
        ]);
    }

    #[Route('/admin/activity/{id}/edit', name: 'app_admin_activity_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editAdmin(Request $request, Activity $activity, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             /** @var UploadedFile|null $file */
            $file = $form->get('file')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/activities',
                        $newFilename
                    );
                    $activity->setFile($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading file');
                }
            }
            
            $entityManager->flush();

            return $this->redirectToRoute('app_course_activities', ['id' => $activity->getCourse()->getId()]);
        }

        return $this->render('activity/backEdit.html.twig', [
            'activity' => $activity,
            'form' => $form,
        ]);
    }

    #[Route('/activity/{id}', name: 'app_activity_delete', methods: ['POST'])]
    public function delete(Request $request, Activity $activity, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($activity->getCourse()->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to delete this activity.');
        }

        if ($this->isCsrfTokenValid('delete'.$activity->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($activity);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_course_activities', ['id' => $activity->getCourse()->getId()]);
    }
}
