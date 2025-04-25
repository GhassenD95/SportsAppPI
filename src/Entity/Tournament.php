<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TournamentRepository::class)]
class Tournament
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $start_date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $end_date = null;

    /**
     * @var Collection<int, Team>
     */
    #[ORM\ManyToMany(targetEntity: Team::class, inversedBy: 'tournaments')]
    private Collection $teams;

    /**
     * @var Collection<int, MatchEvent>
     */
    #[ORM\OneToMany(targetEntity: MatchEvent::class, mappedBy: 'tournament')]
    private Collection $matchEvents;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->matchEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTimeInterface $start_date): static
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeInterface $end_date): static
    {
        $this->end_date = $end_date;

        return $this;
    }

    /**
     * @return Collection<int, Team>
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): static
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
        }

        return $this;
    }

    public function removeTeam(Team $team): static
    {
        $this->teams->removeElement($team);

        return $this;
    }
    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return Collection<int, MatchEvent>
     */
    public function getMatchEvents(): Collection
    {
        return $this->matchEvents;
    }

    public function addMatchEvent(MatchEvent $matchEvent): static
    {
        if (!$this->matchEvents->contains($matchEvent)) {
            $this->matchEvents->add($matchEvent);
            $matchEvent->setTournament($this);
        }

        return $this;
    }

    public function removeMatchEvent(MatchEvent $matchEvent): static
    {
        if ($this->matchEvents->removeElement($matchEvent)) {
            // set the owning side to null (unless already changed)
            if ($matchEvent->getTournament() === $this) {
                $matchEvent->setTournament(null);
            }
        }

        return $this;
    }
}
