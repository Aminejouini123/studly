<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Activity title is required")]
    #[Assert\Length(min: 3, max: 255, minMessage: "Title must be at least 3 characters long", maxMessage: "Title cannot be longer than {{ limit }} characters")]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Description is required")]
    #[Assert\Length(min: 10, minMessage: "Description must be at least 10 characters long")]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $file = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "Link must be a valid URL")]
    #[Assert\Length(max: 255, maxMessage: "Link cannot be longer than {{ limit }} characters")]
    private ?string $link = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Duration is required")]
    #[Assert\Positive(message: "Duration must be positive")]
    #[Assert\Type(type: 'integer', message: "Duration must be an integer")]
    private ?int $duration = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Status is required")]
    #[Assert\Choice(choices: ["to do", "in progress", "completed"], message: "Choose a valid status")]
    #[Assert\Length(max: 255, maxMessage: "Status cannot be longer than {{ limit }} characters")]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Difficulty is required")]
    #[Assert\Choice(choices: ["Easy", "Medium", "Hard"], message: "Choose a valid difficulty")]
    #[Assert\Length(max: 255, maxMessage: "Difficulty cannot be longer than {{ limit }} characters")]
    private ?string $difficulty = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Level is required")]
    #[Assert\Choice(choices: ["Beginner", "Intermediate", "Advanced"], message: "Choose a valid level")]
    #[Assert\Length(max: 255, maxMessage: "Level cannot be longer than {{ limit }} characters")]
    private ?string $level = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Type is required")]
    #[Assert\Choice(choices: ["quiz", "challenge", "mini_project"], message: "Choose a valid activity type")]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $instructions = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $expectedOutput = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $hints = null;

    #[ORM\ManyToOne(inversedBy: 'activities')]
    private ?Course $course = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): static
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

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

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

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(?string $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): static
    {
        $this->level = $level;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): static
    {
        $this->instructions = $instructions;

        return $this;
    }

    public function getExpectedOutput(): ?string
    {
        return $this->expectedOutput;
    }

    public function setExpectedOutput(?string $expectedOutput): static
    {
        $this->expectedOutput = $expectedOutput;

        return $this;
    }

    public function getHints(): ?string
    {
        return $this->hints;
    }

    public function setHints(?string $hints): static
    {
        $this->hints = $hints;

        return $this;
    }
}
