<?php

namespace App\Entity;

use App\Repository\ExamRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExamRepository::class)]
class Exam
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line property.unusedType */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Exam title is required")]
    #[Assert\Length(min: 3, max: 255, minMessage: "Title must be at least 3 characters long", maxMessage: "Title cannot be longer than {{ limit }} characters")]
    private string $title = '';

    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\NotBlank(message: "Exam date is required")]
    #[Assert\GreaterThan("today", message: "Exam date must be in the future")]
    private \DateTimeImmutable $date;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Duration is required")]
    #[Assert\Positive(message: "Duration must be positive")]
    #[Assert\Type(type: 'integer', message: "Duration must be an integer")]
    private int $duration = 0;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: "Grade is required")]
    #[Assert\Range(min: 0, max: 20, notInRangeMessage: "Grade must be between {{ min }} and {{ max }}")]
    #[Assert\Type(type: 'float', message: "Grade must be a valid number")]
    private ?float $grade = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Difficulty is required")]
    #[Assert\Length(max: 50, maxMessage: "Difficulty cannot be longer than {{ limit }} characters")]
    private string $difficulty = '';

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Status is required")]
    #[Assert\Length(max: 50, maxMessage: "Status cannot be longer than {{ limit }} characters")]
    private string $status = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $file = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Link is required")]
    #[Assert\Url(message: "Link must be a valid URL")]
    #[Assert\Length(max: 255, maxMessage: "Link cannot be longer than {{ limit }} characters")]
    private ?string $link = null;

    #[ORM\ManyToOne(inversedBy: 'exams')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Course $course = null;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
    }

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

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        if ($date instanceof \DateTime) {
            $this->date = \DateTimeImmutable::createFromMutable($date);
        } else {
            $this->date = $date;
        }

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getGrade(): ?float
    {
        return $this->grade;
    }

    public function setGrade(?float $grade): static
    {
        $this->grade = $grade;

        return $this;
    }

    public function getDifficulty(): string
    {
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty): static
    {
        $this->difficulty = $difficulty;

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

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;

        return $this;
    }
}
