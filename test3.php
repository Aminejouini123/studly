<?php
require 'vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__.'/.env');
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');

$task = $em->getRepository(\App\Entity\ProjectTask::class)->findOneBy(['title' => 'gestion utulistae']);

if (!$task) {
    echo "Task not found\n";
    exit;
}

$task->setStatus(\App\Entity\ProjectTask::STATUS_DONE);
$task->setCompletedAt(new \DateTime());
$em->flush();

echo "Task manually set to DONE to match user screenshot.\n";
