<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Course>
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * Count courses for a given user.
     */
    public function countByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find the "top" course for a user.
     *
     * Here we define "top" as the course with the highest coefficient,
     * falling back to most recently created when coefficients are equal.
     */
    public function findTopCourseForUser(User $user): ?Course
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.coefficient', 'DESC')
            ->addOrderBy('c.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Course[] Returns an array of Course objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Course
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
