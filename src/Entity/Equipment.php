<?php

namespace App\Entity;

use App\Enum\EquipmentState;
use App\Enum\EquipmentType;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $state = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image_url = null;

    #[ORM\ManyToOne(inversedBy: 'equipements')]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    private ?Facility $facility = null;

    #[ORM\ManyToOne(inversedBy: 'equipements')]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    private ?Team $team = null;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setType(string $type): static
    {
        if (!in_array($type, EquipmentType::values())) {
            throw new \InvalidArgumentException("Invalid equipment type");
        }
        $this->type = $type;
        return $this;
    }

    public function setState(string $state): static
    {
        if (!in_array($state, EquipmentState::values())) {
            throw new \InvalidArgumentException("Invalid equipment state");
        }
        $this->state = $state;
        return $this;
    }
    public function getTypeLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getStateLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->state));
    }
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImageUrl(?string $image_url): static
    {
        $this->image_url = $image_url;

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

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

        return $this;
    }


}
