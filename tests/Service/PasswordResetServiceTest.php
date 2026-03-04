<?php

namespace App\Tests\Service;

use App\Entity\PasswordResetToken;
use App\Service\PasswordResetService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PasswordResetServiceTest extends TestCase
{
    private PasswordResetService $service;

    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $entityManager;

    /** @var EntityRepository<PasswordResetToken>&MockObject */
    private EntityRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $this->entityManager->method('getRepository')
            ->willReturn($this->repository);

        $this->service = new PasswordResetService($this->entityManager);
    }

    public function testFindValidTokenReturnsNullIfNotFound(): void
    {
        $this->repository->method('findOneBy')
            ->willReturn(null);

        $result = $this->service->findValidToken('random-token');
        $this->assertNull($result);
    }

    public function testFindValidTokenReturnsNullIfExpired(): void
    {
        $token = new PasswordResetToken();
        $token->setExpiresAt((new \DateTimeImmutable())->modify('-1 hour'));

        $this->repository->method('findOneBy')
            ->willReturn($token);
        $result = $this->service->findValidToken('expired-token');
        $this->assertNull($result);
    }

    public function testFindValidTokenReturnsTokenIfValid(): void
    {
        $token = new PasswordResetToken();
        $token->setExpiresAt((new \DateTimeImmutable())->modify('+1 hour'));

        $this->repository->method('findOneBy')->willReturn($token);

        $result = $this->service->findValidToken('valid-token');
        $this->assertSame($token, $result);
    }
}
