<?php

namespace App\Service;

use App\Entity\Group;

class GroupManager
{
    public function validate(Group $group): bool
    {
        if ($group->getCapacity() === null || $group->getCapacity() <= 0) {
            throw new \InvalidArgumentException('La capacité doit être positive');
        }

        if ($group->getCapacity() > 200) {
            throw new \InvalidArgumentException('La capacité ne peut pas dépasser 200');
        }

        return true;
    }
}