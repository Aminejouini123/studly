<?php
use App\Kernel;
use App\Entity\Group;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

$ids = [1, 2, 3];
foreach ($ids as $id) {
    $group = $em->getRepository(Group::class)->find($id);
    if ($group) {
        echo "Found Group $id: " . $group->getCategory() . "\n";
    } else {
        echo "Group $id NOT FOUND\n";
    }
}
$kernel->shutdown();
