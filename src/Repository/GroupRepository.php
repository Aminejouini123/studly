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
     * @return Group[]
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
     * Find all groups where the user is either the creator OR a member
     * @return Group[]
     */
    public function findUserGroups(User $user): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.members', 'm')
            ->where('g.creator = :user')
            ->orWhere('m.id = :user')
            ->setParameter('user', $user)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }


    /**
     * @return Group[]
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
     * @return Group[]
     */
    public function searchByCategory(string $searchTerm): array
    {
        return $this->createQueryBuilder('g')
            ->select('g', 'm')
            ->leftJoin('g.members', 'm')
            ->andWhere('g.category LIKE :term')
            ->setParameter('term', '%' . $searchTerm . '%')
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Group[]
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





}
