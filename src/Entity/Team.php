<?php

namespace App\Entity;

use App\DataFixtures\UserFixtures;
use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'teams')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?User $coach = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'team')]
    private Collection $players;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoUrl = null;



    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: ['football', 'basketball', 'volleyball'], message: 'Choose a valid sport.')]
    private ?string $sport = null;

    /**
     * @var Collection<int, Equipment>
     */
    #[ORM\OneToMany(targetEntity: Equipment::class, mappedBy: 'team')]
    private Collection $equipements;

    /**
     * @var Collection<int, Tournament>
     */
    #[ORM\ManyToMany(targetEntity: Tournament::class, mappedBy: 'teams')]
    private Collection $tournaments;

    /**
     * @var Collection<int, MatchEvent>
     */
    #[ORM\OneToMany(targetEntity: MatchEvent::class, mappedBy: 'homeTeam', orphanRemoval: true)]
    private Collection $matchEvents;

    /**
     * @var Collection<int, Training>
     */
    #[ORM\OneToMany(targetEntity: Training::class, mappedBy: 'team', orphanRemoval: true)]
    private Collection $trainings;

    /**
     * @var Collection<int, Injuries>
     */
    #[ORM\OneToMany(targetEntity: Injuries::class, mappedBy: 'team')]
    private Collection $injuries;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->equipements = new ArrayCollection();
        $this->tournaments = new ArrayCollection();
        $this->matchEvents = new ArrayCollection();
        $this->trainings = new ArrayCollection();
        $this->injuries = new ArrayCollection();
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

    public function getCoach(): ?User
    {
        return $this->coach;
    }

    public function setCoach(?User $coach): static
    {
        $this->coach = $coach;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(User $player): static
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
            $player->setTeam($this);
        }

        return $this;
    }

    public function removePlayer(User $player): static
    {
        if ($this->players->removeElement($player)) {
            // set the owning side to null (unless already changed)
            if ($player->getTeam() === $this) {
                $player->setTeam(null);
            }
        }

        return $this;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): static
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }

    public function getSport(): ?string
    {
        return $this->sport;
    }

    public function setSport(string $sport): static
    {
        $this->sport = $sport;

        return $this;
    }

    /**
     * @return Collection<int, Equipment>
     */
    public function getEquipements(): Collection
    {
        return $this->equipements;
    }

    public function addEquipement(Equipment $equipement): static
    {
        if (!$this->equipements->contains($equipement)) {
            $this->equipements->add($equipement);
            $equipement->setTeam($this);
        }

        return $this;
    }

    public function removeEquipement(Equipment $equipement): static
    {
        if ($this->equipements->removeElement($equipement)) {
            // set the owning side to null (unless already changed)
            if ($equipement->getTeam() === $this) {
                $equipement->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tournament>
     */
    public function getTournaments(): Collection
    {
        return $this->tournaments;
    }

    public function addTournament(Tournament $tournament): static
    {
        if (!$this->tournaments->contains($tournament)) {
            $this->tournaments->add($tournament);
            $tournament->addTeam($this);
        }

        return $this;
    }

    public function removeTournament(Tournament $tournament): static
    {
        if ($this->tournaments->removeElement($tournament)) {
            $tournament->removeTeam($this);
        }

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
            $matchEvent->setHomeTeam($this);
        }

        return $this;
    }

    public function removeMatchEvent(MatchEvent $matchEvent): static
    {
        if ($this->matchEvents->removeElement($matchEvent)) {
            // set the owning side to null (unless already changed)
            if ($matchEvent->getHomeTeam() === $this) {
                $matchEvent->setHomeTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Training>
     */
    public function getTrainings(): Collection
    {
        return $this->trainings;
    }

    public function addTraining(Training $training): static
    {
        if (!$this->trainings->contains($training)) {
            $this->trainings->add($training);
            $training->setTeam($this);
        }

        return $this;
    }

    public function removeTraining(Training $training): static
    {
        if ($this->trainings->removeElement($training)) {
            // set the owning side to null (unless already changed)
            if ($training->getTeam() === $this) {
                $training->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Injuries>
     */
    public function getInjuries(): Collection
    {
        return $this->injuries;
    }

    public function addInjury(Injuries $injury): static
    {
        if (!$this->injuries->contains($injury)) {
            $this->injuries->add($injury);
            $injury->setTeam($this);
        }

        return $this;
    }

    public function removeInjury(Injuries $injury): static
    {
        if ($this->injuries->removeElement($injury)) {
            if ($injury->getTeam() === $this) {
                $injury->setTeam(null);
            }
        }

        return $this;
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class]; // ✅ Correct
    }
}
