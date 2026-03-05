<?php
require 'vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__.'/.env');
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');

// find the task "gestion utulistae"
$task = $em->getRepository(\App\Entity\ProjectTask::class)->findOneBy(['title' => 'gestion utulistae']);

if (!$task) {
    echo "Task not found\n";
    exit;
}

echo "Task Title: " . $task->getTitle() . "\n";
echo "Task Status: " . $task->getStatus() . "\n";
echo "Task completedAt: " . ($task->getCompletedAt() ? $task->getCompletedAt()->format('Y-m-d H:i:s') : 'null') . "\n";
echo "Task deliverable: " . $task->getDeliverable() . "\n";

// Now act as if we are uploading
echo "\n--- Simulating status revert to TO_DO ---\n";
$task->setStatus(\App\Entity\ProjectTask::STATUS_TO_DO);
$task->setCompletedAt(null);

$user = $task->getAssignedUser();
echo "Assigned user object? " . ($user ? "yes" : "no") . "\n";

// Emulate getOldStatus behaviour
$uow = $em->getUnitOfWork();
$uow->computeChangeSets();
$changeset = $uow->getEntityChangeSet($task);

echo "Changeset status: " . print_r($changeset['status'] ?? null, true) . "\n";

$em->flush();
echo "After flush: Status is " . $task->getStatus() . " completedAt: " . ($task->getCompletedAt() ? $task->getCompletedAt()->format('Y-m-d H:i:s') : 'null') . "\n";

