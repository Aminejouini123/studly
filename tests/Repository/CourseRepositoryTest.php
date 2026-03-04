<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Course;
use App\Entity\User;
use App\Repository\CourseRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class CourseRepositoryTest extends TestCase
{
    public function testCountByUserReturnsIntegerCount(): void
    {
        $user = (new User())->setEmail('u@example.com');

        $query = $this->createMock(Query::class);
        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn('7');

        $qb = $this->createMock(QueryBuilder::class);
        $qb->expects($this->once())->method('select')->with('COUNT(c.id)')->willReturnSelf();
        $qb->expects($this->once())->method('andWhere')->with('c.user = :user')->willReturnSelf();
        $qb->expects($this->once())->method('setParameter')->with('user', $user)->willReturnSelf();
        $qb->expects($this->once())->method('getQuery')->willReturn($query);

        $repo = $this->getMockBuilder(CourseRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $repo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('c')
            ->willReturn($qb);

        $result = $repo->countByUser($user);

        $this->assertSame(7, $result);
    }

    public function testFindTopCourseForUserReturnsCourseOrNull(): void
    {
        $user = (new User())->setEmail('u@example.com');
        $course = (new Course())->setName('Top Course');

        $query = $this->createMock(Query::class);
        $query->expects($this->once())
            ->method('getOneOrNullResult')
            ->willReturn($course);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('andWhere')->with('c.user = :user')->willReturnSelf();
        $qb->method('setParameter')->with('user', $user)->willReturnSelf();
        $qb->method('orderBy')->with('c.coefficient', 'DESC')->willReturnSelf();
        $qb->method('addOrderBy')->with('c.createdAt', 'DESC')->willReturnSelf();
        $qb->method('setMaxResults')->with(1)->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $repo = $this->getMockBuilder(CourseRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $repo->method('createQueryBuilder')->with('c')->willReturn($qb);

        $result = $repo->findTopCourseForUser($user);

        $this->assertSame($course, $result);
    }

    public function testFindRecentByUserReturnsOrderedArray(): void
    {
        $user = (new User())->setEmail('u@example.com');
        $courses = [
            (new Course())->setName('A'),
            (new Course())->setName('B'),
        ];

        $query = $this->createMock(Query::class);
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn($courses);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('andWhere')->with('c.user = :user')->willReturnSelf();
        $qb->method('setParameter')->with('user', $user)->willReturnSelf();
        $qb->method('orderBy')->with('c.createdAt', 'DESC')->willReturnSelf();
        $qb->method('setMaxResults')->with(4)->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $repo = $this->getMockBuilder(CourseRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $repo->method('createQueryBuilder')->with('c')->willReturn($qb);

        $result = $repo->findRecentByUser($user, 4);

        $this->assertCount(2, $result);
        $this->assertSame('A', $result[0]->getName());
        $this->assertSame('B', $result[1]->getName());
    }
}

