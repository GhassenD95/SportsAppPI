<?php

namespace App\Entity;

use App\Repository\TeamPerformanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamPerformanceRepository::class)]
class TeamPerformance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $team = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $performanceDate = null;

    #[ORM\Column]
    private ?int $goalsScored = null;

    #[ORM\Column]
    private ?int $goalsConceded = null;

    #[ORM\Column]
    private ?int $shotsOnTarget = null;

    #[ORM\Column]
    private ?int $shotsConceded = null;

    #[ORM\Column]
    private ?int $possessionPercentage = null;

    #[ORM\Column]
    private ?int $passesCompleted = null;

    #[ORM\Column]
    private ?int $tackles = null;

    #[ORM\Column]
    private ?int $interceptions = null;

    #[ORM\Column]
    private ?int $fouls = null;

    #[ORM\Column]
    private ?int $yellowCards = null;

    #[ORM\Column]
    private ?int $redCards = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1)]
    private ?string $rating = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getGoalsConceded(): ?int
    {
        return $this->goalsConceded;
    }

    public function setGoalsConceded(int $goalsConceded): static
    {
        $this->goalsConceded = $goalsConceded;
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

    public function getShotsConceded(): ?int
    {
        return $this->shotsConceded;
    }

    public function setShotsConceded(int $shotsConceded): static
    {
        $this->shotsConceded = $shotsConceded;
        return $this;
    }

    public function getPossessionPercentage(): ?int
    {
        return $this->possessionPercentage;
    }

    public function setPossessionPercentage(int $possessionPercentage): static
    {
        $this->possessionPercentage = $possessionPercentage;
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

    public function getFouls(): ?int
    {
        return $this->fouls;
    }

    public function setFouls(int $fouls): static
    {
        $this->fouls = $fouls;
        return $this;
    }

    public function getYellowCards(): ?int
    {
        return $this->yellowCards;
    }

    public function setYellowCards(int $yellowCards): static
    {
        $this->yellowCards = $yellowCards;
        return $this;
    }

    public function getRedCards(): ?int
    {
        return $this->redCards;
    }

    public function setRedCards(int $redCards): static
    {
        $this->redCards = $redCards;
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