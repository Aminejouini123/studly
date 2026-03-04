<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Entity\Course;
use App\Entity\User;
use App\EventListener\CourseCreatedListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

final class CourseCreatedListenerTest extends TestCase
{
    public function testPostPersistSendsEmailWhenCourseHasUserWithEmail(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $listener = new CourseCreatedListener($mailer);

        $user = (new User())
            ->setEmail('student@example.com')
            ->setFirstName('Test')
            ->setLastName('User');

        $course = (new Course())
            ->setName('Algorithms')
            ->setUser($user);

        $mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($email): bool {
                if (!$email instanceof TemplatedEmail) {
                    return false;
                }

                $to = $email->getTo();
                $subject = $email->getSubject();

                return isset($to[0])
                    && $to[0]->getAddress() === 'student@example.com'
                    && is_string($subject)
                    && str_contains($subject, 'Algorithms');
            }));

        $listener->postPersist($course, $this->createPostPersistArgs($course));
    }

    public function testPostPersistDoesNotSendEmailWhenNoUser(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $listener = new CourseCreatedListener($mailer);

        $course = (new Course())->setName('Networks');

        $mailer->expects($this->never())->method('send');

        $listener->postPersist($course, $this->createPostPersistArgs($course));
    }

    public function testPostPersistDoesNotSendEmailWhenUserEmailMissing(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $listener = new CourseCreatedListener($mailer);

        $user = (new User())
            ->setFirstName('No')
            ->setLastName('Email');

        $course = (new Course())
            ->setName('Databases')
            ->setUser($user);

        $mailer->expects($this->never())->method('send');

        $listener->postPersist($course, $this->createPostPersistArgs($course));
    }

    private function createPostPersistArgs(Course $course): PostPersistEventArgs
    {
        $objectManager = $this->createMock(ObjectManager::class);

        return new PostPersistEventArgs($course, $objectManager);
    }
}
