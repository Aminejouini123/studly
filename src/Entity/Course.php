<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Course name is required")]
    #[Assert\Length(min: 3, max: 255, minMessage: "Name must be at least 3 characters long", maxMessage: "Name cannot be longer than {{ limit }} characters")]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $courseFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Link is required")]
    #[Assert\Url(message: "The link must be a valid URL")]
    #[Assert\Length(max: 255, maxMessage: "Link cannot be longer than {{ limit }} characters")]
    private ?string $courseLink = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Teacher email is required")]
    #[Assert\Email(message: "The email '{{ value }}' is not valid.")]
    #[Assert\Length(max: 255, maxMessage: "Email cannot be longer than {{ limit }} characters")]
    private ?string $teacherEmail = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Semester is required")]
    #[Assert\Length(max: 255, maxMessage: "Semester cannot be longer than {{ limit }} characters")]
    private ?string $semester = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Difficulty level is required")]
    #[Assert\Length(max: 255, maxMessage: "Difficulty level cannot be longer than {{ limit }} characters")]
    private ?string $difficultyLevel = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Course type is required")]
    #[Assert\Length(max: 255, maxMessage: "Type cannot be longer than {{ limit }} characters")]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Priority is required")]
    #[Assert\Length(max: 255, maxMessage: "Priority cannot be longer than {{ limit }} characters")]
    private ?string $priority = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Coefficient is required")]
    #[Assert\Positive(message: "Coefficient must be positive")]
    #[Assert\Type(type: 'float', message: "Coefficient must be a valid number")]
    private ?float $coefficient = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Status is required")]
    #[Assert\Length(max: 50, maxMessage: "Status cannot be longer than {{ limit }} characters")]
    private ?string $status = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Duration is required")]
    #[Assert\Positive(message: "Duration must be positive")]
    #[Assert\Type(type: 'integer', message: "Duration must be an integer")]
    private ?int $duration = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\NotBlank(message: "Comment is required")]
    #[Assert\Length(max: 2000, maxMessage: "Comment cannot be longer than {{ limit }} characters")]
    private ?string $comment = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'course', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $activities;

    /**
     * @var Collection<int, Exam>
     */
    #[ORM\OneToMany(targetEntity: Exam::class, mappedBy: 'course', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $exams;

    public function __construct()
    {
        $this->activities = new ArrayCollection();
        $this->exams = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCourseFile(): ?string
    {
        return $this->courseFile;
    }

    public function setCourseFile(?string $courseFile): static
    {
        $this->courseFile = $courseFile;

        return $this;
    }

    public function getCourseLink(): ?string
    {
        return $this->courseLink;
    }

    public function setCourseLink(?string $courseLink): static
    {
        $this->courseLink = $courseLink;

        return $this;
    }

    public function getTeacherEmail(): ?string
    {
        return $this->teacherEmail;
    }

    public function setTeacherEmail(?string $teacherEmail): static
    {
        $this->teacherEmail = $teacherEmail;

        return $this;
    }

    public function getSemester(): ?string
    {
        return $this->semester;
    }

    public function setSemester(?string $semester): static
    {
        $this->semester = $semester;

        return $this;
    }

    public function getDifficultyLevel(): ?string
    {
        return $this->difficultyLevel;
    }

    public function setDifficultyLevel(?string $difficultyLevel): static
    {
        $this->difficultyLevel = $difficultyLevel;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(?string $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getCoefficient(): ?float
    {
        return $this->coefficient;
    }

    public function setCoefficient(?float $coefficient): static
    {
        $this->coefficient = $coefficient;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): static
    {
        if (!$this->activities->contains($activity)) {
            $this->activities->add($activity);
            $activity->setCourse($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): static
    {
        if ($this->activities->removeElement($activity)) {
            // set the owning side to null (unless already changed)
            if ($activity->getCourse() === $this) {
                $activity->setCourse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Exam>
     */
    public function getExams(): Collection
    {
        return $this->exams;
    }

    public function addExam(Exam $exam): static
    {
        if (!$this->exams->contains($exam)) {
            $this->exams->add($exam);
            $exam->setCourse($this);
        }

        return $this;
    }

    public function removeExam(Exam $exam): static
    {
        if ($this->exams->removeElement($exam)) {
            // set the owning side to null (unless already changed)
            if ($exam->getCourse() === $this) {
                $exam->setCourse(null);
            }
        }

        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'courses')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
