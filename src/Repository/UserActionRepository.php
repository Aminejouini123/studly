<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserAction>
 */
class UserActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAction::class);
    }

    /**
     * @return UserAction[] Returns an array of UserAction objects
     */
    public function findRecentActionsByUser(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.user = :user')
            ->setParameter('user', $user)
            ->orderBy('u.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return array<string, int>
     */
    public function countActionsForLastYear(User $user): array
    {
        $startDate = (new \DateTimeImmutable())->modify('-370 days')->setTime(0, 0, 0);

        $results = $this->createQueryBuilder('u')
            ->where('u.user = :user')
            ->andWhere('u.createdAt >= :startDate')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($results as $action) {
            $date = $action->getCreatedAt()->format('Y-m-d');
            $data[$date] = ($data[$date] ?? 0) + 1;
        }

        return $data;
    }
}
