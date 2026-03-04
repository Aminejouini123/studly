<?php

namespace App\Service;

use App\Entity\User;
use InvalidArgumentException;

class UserManager
{
    /**
     * Validates business rules for a User entity.
     * 
     * Rules:
     * 1. Email is mandatory.
     * 2. Email must be valid.
     * 3. Plain password must be at least 6 characters (for creation).
     * 4. First name and Last name are mandatory (at least 2 chars).
     *
     * @param User $user
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validate(User $user): bool
    {
        if (empty($user->getEmail())) {
            throw new InvalidArgumentException("L'email est obligatoire");
        }

        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("L'email n'est pas valide");
        }

        $plainPassword = $user->getPlainPassword();
        if ($plainPassword !== null && strlen($plainPassword) < 6) {
            throw new InvalidArgumentException("Le mot de passe doit faire au moins 6 caractères");
        }

        if (empty($user->getFirstName()) || strlen($user->getFirstName()) < 2) {
            throw new InvalidArgumentException("Le prénom est obligatoire (min 2 caractères)");
        }

        if (empty($user->getLastName()) || strlen($user->getLastName()) < 2) {
            throw new InvalidArgumentException("Le nom est obligatoire (min 2 caractères)");
        }

        return true;
    }
}
