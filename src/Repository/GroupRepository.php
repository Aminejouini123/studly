<?php

namespace App\Repository;

use App\Entity\Group;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Group>
 */
class GroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    /**
     * Find all groups created by a specific user
     */
    public function findByCreator(User $creator): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.creator = :creator')
            ->setParameter('creator', $creator)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Find all groups ordered by creation date
     */
    public function findAllOrderedByCreation(): array
    {
        return $this->createQueryBuilder('g')
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Find all groups sorted by a specific field
     */
    public function findAllSorted(string $sortField, string $direction = 'ASC'): array
    {
        $validFields = ['category', 'createdAt', 'capacity', 'id'];
        if (!in_array($sortField, $validFields)) {
            $sortField = 'createdAt';
        }

        return $this->createQueryBuilder('g')
            ->orderBy('g.' . $sortField, $direction)
            ->getQuery()
            ->getResult();
    }

    /**
     * Search groups by category
     */
    public function searchByCategory(string $category): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.category LIKE :category')
            ->setParameter('category', '%' . $category . '%')
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }


    //    /**
//     * @return Group[] Returns an array of Group objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    //    public function findOneBySomeField($value): ?Group
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
