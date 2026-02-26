<?php
use App\Kernel;
use App\Entity\Group;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

$groups = $em->getRepository(Group::class)->findAll();
echo "Groups List:\n";
foreach ($groups as $g) {
    echo "ID: " . $g->getId() . " | Category: " . $g->getCategory() . " | Creator: " . ($g->getCreator() ? $g->getCreator()->getEmail() : 'NONE') . "\n";
}
$kernel->shutdown();
