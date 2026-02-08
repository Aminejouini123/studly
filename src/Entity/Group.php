<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $capacity = null;

    #[ORM\Column(length: 255)]
    private ?string $groupPhoto = null;

    #[ORM\Column(length: 255)]
    private ?string $category = null;

    #[ORM\OneToOne(mappedBy: 'group', cascade: ['persist', 'remove'])]
    private ?MemberGroup $memberGroup = null;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'group')]
    private Collection $projects;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getGroupPhoto(): ?string
    {
        return $this->groupPhoto;
    }

    public function setGroupPhoto(string $groupPhoto): static
    {
        $this->groupPhoto = $groupPhoto;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getMemberGroup(): ?MemberGroup
    {
        return $this->memberGroup;
    }

    public function setMemberGroup(?MemberGroup $memberGroup): static
    {
        // unset the owning side of the relation if necessary
        if ($memberGroup === null && $this->memberGroup !== null) {
            $this->memberGroup->setGroup(null);
        }

        // set the owning side of the relation if necessary
        if ($memberGroup !== null && $memberGroup->getGroup() !== $this) {
            $memberGroup->setGroup($this);
        }

        $this->memberGroup = $memberGroup;

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setGroup($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getGroup() === $this) {
                $project->setGroup(null);
            }
        }

        return $this;
    }
}
