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
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre de l'examen est obligatoire")]
    #[Assert\Length(min: 3, minMessage: "Le titre doit contenir au moins 3 caractères")]
    private ?string $title = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La date de l'examen est obligatoire")]
    #[Assert\GreaterThan("today", message: "La date de l'examen doit être dans le futur")]
    private ?\DateTime $date = null;

    #[ORM\Column]
    #[Assert\Positive(message: "La durée doit être positive")]
    private ?int $duration = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0, max: 20, notInRangeMessage: "La note doit être comprise entre {{ min }} et {{ max }}")]
    private ?float $grade = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "La difficulté est obligatoire")]
    private ?string $difficulty = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le statut est obligatoire")]
    private ?string $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $file = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "Le lien doit être une URL valide")]
    private ?string $link = null;

    #[ORM\ManyToOne(inversedBy: 'exams')]
    private ?Course $course = null;

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

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDuration(): ?int
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

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty): static
    {
        $this->difficulty = $difficulty;

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
