<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
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
     * Count events for a given user.
     */
    public function countByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns weekly duration stats (in minutes) for a given user.
     *
     * Each row contains:
     *  - year  (ISO week-numbering year)
     *  - week  (ISO week number)
     *  - totalMinutes (sum of durations in minutes)
     *
     * @return array<int, array{year:int, week:int, totalMinutes:int}>
     */
    public function getWeeklyDurationMinutesForUser(User $user): array
    {
        // Fetch only the needed fields to keep the query light
        $rows = $this->createQueryBuilder('e')
            ->select('e.date AS date', 'e.duration AS duration')
            ->andWhere('e.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $stats = [];

        foreach ($rows as $row) {
            /** @var \DateTimeInterface $date */
            $date = $row['date'];

            // ISO-8601 week-numbering year and week
            $weekYear = (int) $date->format('o');
            $weekNum = (int) $date->format('W');

            $key = sprintf('%d-W%02d', $weekYear, $weekNum);

            if (!isset($stats[$key])) {
                $stats[$key] = [
                    'year' => $weekYear,
                    'week' => $weekNum,
                    'totalMinutes' => 0,
                ];
            }

            $stats[$key]['totalMinutes'] += (int) $row['duration'];
        }

        // Ensure results are sorted by year then week
        usort($stats, static function (array $a, array $b): int {
            return [$a['year'], $a['week']] <=> [$b['year'], $b['week']];
        });

        return $stats;
    }


    /**
     * @return Event[]
     */
    public function findUpcomingByUser(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->andWhere('e.date >= :today')
            ->setParameter('user', $user)
            ->setParameter('today', new \DateTime('today'))
            ->orderBy('e.date', 'ASC')
            ->addOrderBy('e.startTime', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Event[] Returns an array of Event objects sorted by priority (High > Medium > Low)
     */
    public function findByUserSortedByPriority(User $user)
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
    public function countCompletedByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.user = :user')
            ->andWhere('e.date < :today')
            ->setParameter('user', $user)
            ->setParameter('today', new \DateTime('today'))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
