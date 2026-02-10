<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }


    /**
     * @return Event[] Returns an array of Event objects sorted by priority (High > Medium > Low)
     */
    public function findByUserSortedByPriority($user)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->addSelect("CASE WHEN e.priority = 'High' THEN 3 WHEN e.priority = 'Medium' THEN 2 WHEN e.priority = 'Low' THEN 1 ELSE 0 END AS HIDDEN priority_weight")
            ->setParameter('user', $user)
            ->orderBy('priority_weight', 'DESC')
            ->addOrderBy('e.date', 'ASC') // Secondary sort by date
            ->getQuery()
            ->getResult();
    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
