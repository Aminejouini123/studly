<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre du projet est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le statut est obligatoire.')]
    private ?string $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: '"{{ value }}" n\'est pas une URL valide.')]
    private ?string $resource = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\GreaterThan('today', message: 'La date limite doit être dans le futur.')]
    private ?\DateTime $deadline = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le type de projet est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'Le type ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Group $group = null;

    /**
     * @var Collection<int, ProjectTask>
     */
    #[ORM\OneToMany(targetEntity: ProjectTask::class, mappedBy: 'project', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $projectTasks;

    public function __construct()
    {
        $this->projectTasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function setResource(?string $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    public function getDeadline(): ?\DateTime
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTime $deadline): static
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): static
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return Collection<int, ProjectTask>
     */
    public function getProjectTasks(): Collection
    {
        return $this->projectTasks;
    }

    public function addProjectTask(ProjectTask $projectTask): static
    {
        if (!$this->projectTasks->contains($projectTask)) {
            $this->projectTasks->add($projectTask);
            $projectTask->setProject($this);
        }

        return $this;
    }

    public function removeProjectTask(ProjectTask $projectTask): static
    {
        $this->projectTasks->removeElement($projectTask);

        return $this;
    }
}
