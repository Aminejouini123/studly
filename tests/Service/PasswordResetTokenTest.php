<?php

namespace App\Tests\Entity;

use App\Entity\PasswordResetToken;
use PHPUnit\Framework\TestCase;

class PasswordResetTokenTest extends TestCase
{
    public function testIsNotExpired(): void
    {
        $token = new PasswordResetToken();
        // Set expiry date to 1 hour from now
        $token->setExpiresAt((new \DateTimeImmutable())->modify('+1 hour'));

        $this->assertFalse($token->isExpired());
    }

    public function testIsExpired(): void
    {
        $token = new PasswordResetToken();
        // Set expiry date to 1 hour ago
        $token->setExpiresAt((new \DateTimeImmutable())->modify('-1 hour'));

        $this->assertTrue($token->isExpired());
    }
}
