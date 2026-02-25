<?php

namespace App\Entity;

use App\Repository\PomodoroSessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PomodoroSessionRepository::class)]
class PomodoroSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pomodoroSessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null; // 'WORK', 'SHORT_BREAK', 'LONG_BREAK'

    #[ORM\Column]
    private ?int $duration = null; // minutes

    #[ORM\Column(length: 255)]
    private ?string $status = 'PENDING'; // 'PENDING', 'COMPLETED'

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endedAt = null;

    #[ORM\Column(nullable: true)]
    private ?float $focusScore = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $focusLogs = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

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

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

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

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeInterface $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeInterface $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getFocusScore(): ?float
    {
        return $this->focusScore;
    }

    public function setFocusScore(?float $focusScore): static
    {
        $this->focusScore = $focusScore;

        return $this;
    }

    public function getFocusLogs(): ?array
    {
        return $this->focusLogs;
    }

    public function setFocusLogs(?array $focusLogs): static
    {
        $this->focusLogs = $focusLogs;

        return $this;
    }
}
