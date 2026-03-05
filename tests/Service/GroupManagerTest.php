<?php

namespace App\Tests\Service;

use App\Entity\Group;
use App\Service\GroupManager;
use PHPUnit\Framework\TestCase;

class GroupManagerTest extends TestCase
{
    public function testValidGroup()
    {
        $group = new Group();
        $group->setCapacity(50);

        $manager = new GroupManager();

        $this->assertTrue($manager->validate($group));
    }

    public function testGroupWithNegativeCapacity()
    {
        $this->expectException(\InvalidArgumentException::class);

        $group = new Group();
        $group->setCapacity(-5);

        $manager = new GroupManager();
        $manager->validate($group);
    }

    public function testGroupWithTooLargeCapacity()
    {
        $this->expectException(\InvalidArgumentException::class);

        $group = new Group();
        $group->setCapacity(300);

        $manager = new GroupManager();
        $manager->validate($group);
    }
}