<?php

declare(strict_types=1);


namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // ID is auto-generated, setId removed

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La capacité est obligatoire.')]
    #[Assert\Positive(message: 'La capacité doit être un nombre positif.')]
    #[Assert\LessThanOrEqual(value: 200, message: 'La capacité ne peut pas dépasser 200 membres.')]
    private int $capacity;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'La photo du groupe doit être une URL valide.')]
    private ?string $groupPhoto = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La catégorie du groupe est obligatoire.')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'La catégorie doit faire au moins {{ limit }} caractères.', maxMessage: 'La catégorie ne peut pas dépasser {{ limit }} caractères.')]
    private string $category;

    #[ORM\ManyToOne(inversedBy: 'groups')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $creator = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'memberGroups')]
    private Collection $members;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'group', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Collection $projects;


    #[ORM\OneToMany(mappedBy: 'group', targetEntity: Message::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $messages;

    #[ORM\OneToMany(mappedBy: 'group', targetEntity: Invitation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $invitations;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->invitations = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->capacity = 1;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCapacity(): int
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

    public function setGroupPhoto(?string $groupPhoto): static
    {
        $this->groupPhoto = $groupPhoto;

        return $this;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }

    public function removeMember(User $member): static
    {
        $this->members->removeElement($member);

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


    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * @return Collection<int, Invitation>
     */
    public function getInvitations(): Collection
    {
        return $this->invitations;
    }
}
