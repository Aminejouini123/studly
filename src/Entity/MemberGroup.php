<?php

namespace App\Entity;

use App\Repository\MemberGroupRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberGroupRepository::class)]
class MemberGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'memberGroup', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Group $group = null;

    // Add other fields as necessary, for now minimal implementation
    // Assuming typical pivot table or member attributes, but "MemberGroup" sounds like a list of members or a settings object for a group.
    // Given usage "each group has one member group"

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(Group $group): static
    {
        $this->group = $group;

        return $this;
    }
}
