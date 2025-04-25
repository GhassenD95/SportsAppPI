<?php

namespace App\Entity;

use App\Repository\MatchEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MatchEventRepository::class)]
class MatchEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "The match date cannot be null")]
    #[Assert\Type("\DateTimeInterface", message: "The date must be a valid DateTime object")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The location cannot be blank")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "The location must be at least {{ limit }} characters long",
        maxMessage: "The location cannot be longer than {{ limit }} characters"
    )]
    private ?string $location = null;

    #[ORM\ManyToOne(inversedBy: 'matchEvents')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "A home team must be selected")]
    private ?Team $homeTeam = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Away team information cannot be null")]
    #[Assert\Type('array', message: "Away team must be an array")]
    #[Assert\Collection(
        fields: [
            "name" => new Assert\NotBlank(message: "Away team name must not be blank"),
            "club" => new Assert\NotBlank(message: "Away team club must not be blank"),
            "logo" => new Assert\Optional([
                new Assert\Type(["type" => "string", "message" => "Logo must be a string or null"]),
            ]),
        ],
        allowExtraFields: false
    )]
    private array $awayTeam = [
        'name' => '',
        'club' => '',
        'logo' => null,
    ];

    #[ORM\ManyToOne(inversedBy: 'matchEvents')]
    private ?Tournament $tournament = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Score cannot be null")]
    #[Assert\Type('array', message: "Score must be an array")]
    #[Assert\Collection(
        fields: [
            "home" => [
                new Assert\NotNull(message: "Home score is required"),
                new Assert\Type(type: "integer", message: "Home score must be an integer"),
                new Assert\GreaterThanOrEqual(0, message: "Home score cannot be negative")
            ],
            "away" => [
                new Assert\NotNull(message: "Away score is required"),
                new Assert\Type(type: "integer", message: "Away score must be an integer"),
                new Assert\GreaterThanOrEqual(0, message: "Away score cannot be negative")
            ]
        ],
        allowExtraFields: false
    )]
    private array $score = [
        'home' => 0,
        'away' => 0
    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getHomeTeam(): ?Team
    {
        return $this->homeTeam;
    }

    public function setHomeTeam(?Team $homeTeam): static
    {
        $this->homeTeam = $homeTeam;
        return $this;
    }

    public function getAwayTeam(): array
    {
        return $this->awayTeam;
    }

    public function setAwayTeam(array $awayTeam): static
    {
        $this->awayTeam = $awayTeam;
        return $this;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): static
    {
        $this->tournament = $tournament;
        return $this;
    }

    public function getScore(): array
    {
        return $this->score;
    }

    public function setScore(array $score): static
    {
        $this->score = $score;
        return $this;
    }

    public function getAwayTeamDisplay(): string
    {
        return sprintf(
            'Name: %s (Club: %s)',
            $this->awayTeam['name'] ?? 'N/A',
            $this->awayTeam['club'] ?? 'N/A'
        );
    }

    public function getScoreDisplay(): string
    {
        return sprintf(
            'Home: %d - Away: %d',
            $this->score['home'] ?? 0,
            $this->score['away'] ?? 0
        );
    }

}
