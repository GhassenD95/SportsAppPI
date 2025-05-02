<?php

namespace App\Entity;

use App\Repository\TrainingRepository;
use App\Validator\TrainingSchedule;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: TrainingRepository::class)]
#[TrainingSchedule]
class Training
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Title must be at least {{ limit }} characters long",
        maxMessage: "Title cannot be longer than {{ limit }} characters"
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(
        value: "today",
        message: "Start time must be in the future"
    )]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    #[Assert\Expression(
        "this.getStartTime() < this.getEndTime()",
        message: "End time must be after start time"
    )]
    #[Assert\Expression(
        "this.getEndTime() <= this.getStartTime().modify('+2 hours')",
        message: "Training session cannot exceed 2 hours"
    )]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\ManyToOne(inversedBy: 'trainings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Facility $facility = null;

    #[ORM\ManyToOne(inversedBy: 'trainings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?User $coach = null;

    #[ORM\ManyToOne(inversedBy: 'trainings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Team $team = null;

    /**
     * @var Collection<int, TrainingExercise>
     */
    #[ORM\OneToMany(targetEntity: TrainingExercise::class, mappedBy: 'training', cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Count(
        min: 1,
        minMessage: "You must specify at least one exercise"
    )]
    private Collection $trainingExercises;


    public function __construct()
    {
        $this->trainingExercises = new ArrayCollection();
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getFacility(): ?Facility
    {
        return $this->facility;
    }

    public function setFacility(?Facility $facility): static
    {
        $this->facility = $facility;

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

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return Collection<int, TrainingExercise>
     */
    public function getTrainingExercises(): Collection
    {
        return $this->trainingExercises;
    }

    public function addTrainingExercise(TrainingExercise $trainingExercise): static
    {
        if (!$this->trainingExercises->contains($trainingExercise)) {
            $this->trainingExercises->add($trainingExercise);
            $trainingExercise->setTraining($this);
        }

        return $this;
    }

    public function removeTrainingExercise(TrainingExercise $trainingExercise): static
    {
        if ($this->trainingExercises->removeElement($trainingExercise)) {
            // set the owning side to null (unless already changed)
            if ($trainingExercise->getTraining() === $this) {
                $trainingExercise->setTraining(null);
            }
        }

        return $this;
    }




}
