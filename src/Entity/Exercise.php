<?php

namespace App\Entity;

use App\Repository\ExerciseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExerciseRepository::class)]
class Exercise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $target = null;

    #[ORM\Column(nullable: true)]
    private ?int $apiId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $instructions = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageUrl = null;

    /**
     * @var Collection<int, TrainingExercise>
     */
    #[ORM\OneToMany(targetEntity: TrainingExercise::class, mappedBy: 'exercise')]
    private Collection $trainingExercises;

    public function __construct()
    {
        $this->trainingExercises = new ArrayCollection();
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

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(string $target): static
    {
        $this->target = $target;

        return $this;
    }

    public function getApiId(): ?int
    {
        return $this->apiId;
    }

    public function setApiId(?int $apiId): static
    {
        $this->apiId = $apiId;

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

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

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
            $trainingExercise->setExercise($this);
        }

        return $this;
    }

    public function removeTrainingExercise(TrainingExercise $trainingExercise): static
    {
        if ($this->trainingExercises->removeElement($trainingExercise)) {
            // set the owning side to null (unless already changed)
            if ($trainingExercise->getExercise() === $this) {
                $trainingExercise->setExercise(null);
            }
        }

        return $this;
    }
}
