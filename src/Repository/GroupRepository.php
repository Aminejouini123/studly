<?php

namespace App\Repository;

use App\Entity\Group;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class GroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    
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

   
    public function findAllOrderedByCreation(): array
    {
        return $this->createQueryBuilder('g')
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    
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


    
}
