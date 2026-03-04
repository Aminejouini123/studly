<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserManager;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class UserManagerTest extends TestCase
{
    private UserManager $userManager;

    protected function setUp(): void
    {
        $this->userManager = new UserManager();
    }

    public function testValidUser(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setPlainPassword('password123');

        $this->assertTrue($this->userManager->validate($user));
    }

    public function testInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("L'email n'est pas valide");

        $user = new User();
        $user->setEmail('invalid-email');

        $this->userManager->validate($user);
    }

    public function testMissingEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("L'email est obligatoire");

        $user = new User();
        // Email not set

        $this->userManager->validate($user);
    }

    public function testShortPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Le mot de passe doit faire au moins 6 caractères");

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPlainPassword('12345');

        $this->userManager->validate($user);
    }

    public function testMissingFirstName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Le prénom est obligatoire (min 2 caractères)");

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPlainPassword('password123');
        $user->setLastName('Doe');
        // FirstName not set

        $this->userManager->validate($user);
    }

    public function testMissingLastName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Le nom est obligatoire (min 2 caractères)");

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPlainPassword('password123');
        $user->setFirstName('John');
        // LastName not set

        $this->userManager->validate($user);
    }
}
