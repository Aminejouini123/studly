<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function countPendingByUser(\App\Entity\User $user): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.assignedUser = :user')
            ->andWhere('t.status != :completed')
            ->setParameter('user', $user)
            ->setParameter('completed', 'Completed')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
