<?php

namespace App\Entity;

use App\Repository\MotivationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MotivationRepository::class)]
class Motivation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $motivationLevel = null;

    #[ORM\Column(length: 255)]
    private ?string $emotion = null;

    #[ORM\Column(length: 255)]
    private ?string $preparation = null;

    #[ORM\Column(length: 255)]
    private ?string $reward = null;

    #[ORM\OneToOne(mappedBy: 'motivation', targetEntity: Event::class, cascade: ['persist', 'remove'])]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'motivations')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMotivationLevel(): ?int
    {
        return $this->motivationLevel;
    }

    public function setMotivationLevel(int $motivationLevel): static
    {
        $this->motivationLevel = $motivationLevel;

        return $this;
    }

    public function getEmotion(): ?string
    {
        return $this->emotion;
    }

    public function setEmotion(string $emotion): static
    {
        $this->emotion = $emotion;

        return $this;
    }

    public function getPreparation(): ?string
    {
        return $this->preparation;
    }

    public function setPreparation(string $preparation): static
    {
        $this->preparation = $preparation;

        return $this;
    }

    public function getReward(): ?string
    {
        return $this->reward;
    }

    public function setReward(string $reward): static
    {
        $this->reward = $reward;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        // unset the owning side of the relation if necessary
        if ($event === null && $this->event !== null) {
            $this->event->setMotivation(null);
        }

        // set the owning side of the relation if necessary
        if ($event !== null && $event->getMotivation() !== $this) {
            $event->setMotivation($this);
        }

        $this->event = $event;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
