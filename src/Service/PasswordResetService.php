<?php

namespace App\Service;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PasswordResetService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function createToken(User $user, int $ttlSeconds = 3600): PasswordResetToken
    {
        $tokenValue = bin2hex(random_bytes(32));

        $reset = new PasswordResetToken();
        $reset->setUser($user);
        $reset->setToken($tokenValue);
        $reset->setCreatedAt(new \DateTimeImmutable());
        $reset->setExpiresAt((new \DateTimeImmutable())->modify("+{$ttlSeconds} seconds"));

        $this->em->persist($reset);
        $this->em->flush();

        return $reset;
    }

    public function findValidToken(string $token): ?PasswordResetToken
    {
        $repo = $this->em->getRepository(PasswordResetToken::class);

        /** @var PasswordResetToken|null $reset */
        $reset = $repo->findOneBy(['token' => $token]);

        if (!$reset) {
            return null;
        }

        if ($reset->isExpired()) {
            return null;
        }

        return $reset;
    }

    public function invalidate(PasswordResetToken $reset): void
    {
        $this->em->remove($reset);
        $this->em->flush();
    }
}

