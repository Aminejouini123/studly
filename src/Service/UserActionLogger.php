<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserAction;
use Doctrine\ORM\EntityManagerInterface;

class UserActionLogger
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function log(User $user, string $type, string $description, ?object $entity = null): void
    {
        $userAction = new UserAction();
        $userAction->setUser($user);
        $userAction->setActionType($type);
        $userAction->setDescription($description);

        if ($entity && method_exists($entity, 'getId')) {
            $userAction->setEntityId($entity->getId());
            $userAction->setEntityType((new \ReflectionClass($entity))->getShortName());
        }

        $this->entityManager->persist($userAction);
        $this->entityManager->flush();
    }
}
