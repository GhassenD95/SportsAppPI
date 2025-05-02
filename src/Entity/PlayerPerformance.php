<?php

namespace App\Entity;

use App\Repository\PlayerPerformanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerPerformanceRepository::class)]
class PlayerPerformance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?User $player = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Team $team = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $performanceDate = null;

    #[ORM\Column]
    private ?int $goalsScored = null;

    #[ORM\Column]
    private ?int $assists = null;

    #[ORM\Column]
    private ?int $minutesPlayed = null;

    #[ORM\Column]
    private ?int $shotsOnTarget = null;

    #[ORM\Column]
    private ?int $passesCompleted = null;

    #[ORM\Column]
    private ?int $tackles = null;

    #[ORM\Column]
    private ?int $interceptions = null;

    #[ORM\Column]
    private ?int $saves = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1)]
    private ?string $rating = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): ?User
    {
        return $this->player;
    }

    public function setPlayer(?User $player): static
    {
        $this->player = $player;
        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;
        return $this;
    }

    public function getPerformanceDate(): ?\DateTimeInterface
    {
        return $this->performanceDate;
    }

    public function setPerformanceDate(\DateTimeInterface $performanceDate): static
    {
        $this->performanceDate = $performanceDate;
        return $this;
    }

    public function getGoalsScored(): ?int
    {
        return $this->goalsScored;
    }

    public function setGoalsScored(int $goalsScored): static
    {
        $this->goalsScored = $goalsScored;
        return $this;
    }

    public function getAssists(): ?int
    {
        return $this->assists;
    }

    public function setAssists(int $assists): static
    {
        $this->assists = $assists;
        return $this;
    }

    public function getMinutesPlayed(): ?int
    {
        return $this->minutesPlayed;
    }

    public function setMinutesPlayed(int $minutesPlayed): static
    {
        $this->minutesPlayed = $minutesPlayed;
        return $this;
    }

    public function getShotsOnTarget(): ?int
    {
        return $this->shotsOnTarget;
    }

    public function setShotsOnTarget(int $shotsOnTarget): static
    {
        $this->shotsOnTarget = $shotsOnTarget;
        return $this;
    }

    public function getPassesCompleted(): ?int
    {
        return $this->passesCompleted;
    }

    public function setPassesCompleted(int $passesCompleted): static
    {
        $this->passesCompleted = $passesCompleted;
        return $this;
    }

    public function getTackles(): ?int
    {
        return $this->tackles;
    }

    public function setTackles(int $tackles): static
    {
        $this->tackles = $tackles;
        return $this;
    }

    public function getInterceptions(): ?int
    {
        return $this->interceptions;
    }

    public function setInterceptions(int $interceptions): static
    {
        $this->interceptions = $interceptions;
        return $this;
    }

    public function getSaves(): ?int
    {
        return $this->saves;
    }

    public function setSaves(int $saves): static
    {
        $this->saves = $saves;
        return $this;
    }

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(string $rating): static
    {
        $this->rating = $rating;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }
} 