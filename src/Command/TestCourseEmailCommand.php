<?php

namespace App\Command;

use App\Entity\Course;
use App\Entity\User;
use App\Repository\CourseRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[AsCommand(
    name: 'app:test-course-email',
    description: 'Envoie un email de test utilisant le template course_created pour visualiser le design.',
)]
class TestCourseEmailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        private CourseRepository $courseRepository,
        private UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'L\'adresse email de destination')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $recipient = $input->getArgument('email');

        // Récupérer un cours et un utilisateur existant pour le contexte
        $course = $this->courseRepository->findOneBy([]);
        $user = $this->userRepository->findOneBy([]);

        if (!$course || !$user) {
            $io->error('Aucun cours ou utilisateur trouvé en base de données pour générer le test.');
            return Command::FAILURE;
        }

        $io->info(sprintf('Envoi de l\'email premium à %s...', $recipient));

        try {
            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@studly.com', 'Studly Admin'))
                ->to($recipient)
                ->subject('Visualisation : Votre cours "' . $course->getName() . '" est prêt !')
                ->htmlTemplate('emails/course_created.html.twig')
                ->context([
                    'course' => $course,
                    'user' => $user, // On simule le propriétaire
                ]);

            $this->mailer->send($email);

            $io->success('L\'email a été envoyé avec succès ! Vérifiez votre boîte de réception.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'envoi : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
