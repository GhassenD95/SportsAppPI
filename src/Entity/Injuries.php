<?php

namespace App\Entity;

use App\Repository\InjuriesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InjuriesRepository::class)]
class Injuries
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'injuries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $player = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $injuryType = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    private ?string $severity = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $injuryDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $expectedRecoveryDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $actualRecoveryDate = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $treatmentPlan = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    private ?string $status = null;

    #[ORM\OneToOne(mappedBy: 'injury', cascade: ['persist', 'remove'])]
    private ?MedicalReport $medicalReport = null;

    #[ORM\ManyToOne(inversedBy: 'injuries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $team = null;

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

    public function getInjuryType(): ?string
    {
        return $this->injuryType;
    }

    public function setInjuryType(string $injuryType): static
    {
        $this->injuryType = $injuryType;
        return $this;
    }

    public function getSeverity(): ?string
    {
        return $this->severity;
    }

    public function setSeverity(string $severity): static
    {
        $this->severity = $severity;
        return $this;
    }

    public function getInjuryDate(): ?\DateTimeInterface
    {
        return $this->injuryDate;
    }

    public function setInjuryDate(\DateTimeInterface $injuryDate): static
    {
        $this->injuryDate = $injuryDate;
        return $this;
    }

    public function getExpectedRecoveryDate(): ?\DateTimeInterface
    {
        return $this->expectedRecoveryDate;
    }

    public function setExpectedRecoveryDate(\DateTimeInterface $expectedRecoveryDate): static
    {
        $this->expectedRecoveryDate = $expectedRecoveryDate;
        return $this;
    }

    public function getActualRecoveryDate(): ?\DateTimeInterface
    {
        return $this->actualRecoveryDate;
    }

    public function setActualRecoveryDate(?\DateTimeInterface $actualRecoveryDate): static
    {
        $this->actualRecoveryDate = $actualRecoveryDate;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getTreatmentPlan(): ?string
    {
        return $this->treatmentPlan;
    }

    public function setTreatmentPlan(string $treatmentPlan): static
    {
        $this->treatmentPlan = $treatmentPlan;
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

    public function getMedicalReport(): ?MedicalReport
    {
        return $this->medicalReport;
    }

    public function setMedicalReport(?MedicalReport $medicalReport): static
    {
        $this->medicalReport = $medicalReport;
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