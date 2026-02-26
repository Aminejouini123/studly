<?php
use App\Kernel;
use App\Entity\User;
use App\Entity\Group;

require __DIR__.'/vendor/autoload.php';

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');

$userEmail = 'mohamedazizraies@gmail.com';
$user = $em->getRepository(User::class)->findOneBy(['email' => $userEmail]);

if (!$user) {
    echo "User $userEmail not found. Creating one...\n";
    $user = new User();
    $user->setEmail($userEmail);
    $user->setPassword('password123'); // Placeholder
    $user->setRoles(['ROLE_ETUDIANT']);
    $em->persist($user);
}

// Check if a group already exists to avoid duplication
$existingGroup = $em->getRepository(Group::class)->findOneBy(['category' => 'Test Group']);

if (!$existingGroup) {
    $group = new Group();
    $group->setCategory('Test Group');
    $group->setCapacity(10);
    $group->setCreator($user);

    $em->persist($group);
    $em->flush();
    echo "Success! Created Group ID: " . $group->getId() . "\n";
} else {
    echo "A 'Test Group' already exists (ID: " . $existingGroup->getId() . ").\n";
}
