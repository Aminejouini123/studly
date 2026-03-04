<?php
use App\Kernel;
use App\Entity\Group;
use App\Entity\User;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');

echo "Checking tables...\n";
$conn = $em->getConnection();
$tables = $conn->iterateAssociative("SHOW TABLES");
foreach ($tables as $table) {
    echo "- " . current($table) . "\n";
}

$repo = $em->getRepository(Group::class);
$groups = $repo->findAll();
echo "Found " . count($groups) . " groups.\n";

if (count($groups) === 0) {
    echo "Creating a default group...\n";
    $userRepo = $em->getRepository(User::class);
    $user = $userRepo->findOneBy(['email' => 'yyninja42@gmail.com']) ?? $userRepo->findOneBy([]);
    
    if (!$user) {
        echo "No user found to assign as creator. Skipping group creation.\n";
    } else {
        $group = new Group();
        $group->setCategory('General');
        $group->setCapacity(10);
        $group->setCreator($user);
        $em->persist($group);
        $em->flush();
        echo "Created group ID: " . $group->getId() . "\n";
    }
} else {
    foreach ($groups as $g) {
        echo "Group ID: " . $g->getId() . " | Category: " . $g->getCategory() . "\n";
    }
}

$kernel->shutdown();
