<?php

namespace App\EventListener;

use App\Entity\Course;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Course::class)]
class CourseCreatedListener
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    public function postPersist(Course $course, PostPersistEventArgs $event): void
    {
        $user = $course->getUser();
        if (!$user instanceof User) {
            return; // Only send email if a user is associated
        }

        $recipientEmail = $user->getEmail();
        if (!$recipientEmail) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@studly.com', 'Studly Notification'))
            ->to($recipientEmail)
            ->subject('Votre cours "' . $course->getName() . '" est maintenant en ligne !')
            ->htmlTemplate('emails/course_created.html.twig')
            ->context([
                'course' => $course,
                'user' => $course->getUser(),
            ]);

        $this->mailer->send($email);
    }
}
