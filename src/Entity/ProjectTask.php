<?php

declare(strict_types=1);


namespace App\Entity;

use App\Repository\ProjectTaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectTaskRepository::class)]
#[ORM\Table(name: '`project_task`')]
#[ORM\HasLifecycleCallbacks]
class ProjectTask
{
    public const STATUS_TO_DO = 'TO_DO';
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_DONE = 'DONE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ID is auto-generated, setId removed

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre de la tâche est obligatoire.')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Le titre doit faire au moins {{ limit }} caractères.', maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.')]
    private string $title;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La description de la tâche est obligatoire.')]
    private string $description;

    #[ORM\Column(length: 255)]
    private string $status;

    #[ORM\ManyToOne(inversedBy: 'projectTasks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Project $project = null;

    #[ORM\ManyToOne(inversedBy: 'assignedProjectTasks')]
    private ?User $assignedUser = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deadline = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliverable = null;

    #[ORM\Column(nullable: true)]
    private ?int $grade = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $attachment = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resourcePath = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getAssignedUser(): ?User
    {
        return $this->assignedUser;
    }

    public function setAssignedUser(?User $assignedUser): static
    {
        $this->assignedUser = $assignedUser;

        return $this;
    }

    public function getDeadline(): ?\DateTimeImmutable
    {
        return $this->deadline;
    }

    protected function setDeadline(?\DateTimeImmutable $deadline): static
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    // completedAt setter removed

    public function getDeliverable(): ?string
    {
        return $this->deliverable;
    }

    public function setDeliverable(?string $deliverable): static
    {
        $this->deliverable = $deliverable;

        return $this;
    }

    public function getGrade(): ?int
    {
        return $this->grade;
    }

    public function setGrade(?int $grade): static
    {
        $this->grade = $grade;

        return $this;
    }

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function setAttachment(?string $attachment): static
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function getResourcePath(): ?string
    {
        return $this->resourcePath;
    }

    public function setResourcePath(?string $resourcePath): static
    {
        $this->resourcePath = $resourcePath;

        return $this;
    }
}
