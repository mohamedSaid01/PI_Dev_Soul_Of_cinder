<?php


// src/Entity/Medecin.php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Medecin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'medecin', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'json')]
    private $typesRendezVous = []; // Types acceptés : "enligne", "presentiel", "hybride"

    #[ORM\OneToMany(mappedBy: 'medecin', targetEntity: RendezVous::class)]
    private $rendezVous;

    public function __construct()
    {
        $this->rendezVous = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTypesRendezVous(): array
    {
        return $this->typesRendezVous;
    }

    public function setTypesRendezVous(array $typesRendezVous): self
    {
        $this->typesRendezVous = $typesRendezVous;

        return $this;
    }

    public function addTypeRendezVous(string $type): self
    {
        if (!in_array($type, $this->typesRendezVous)) {
            $this->typesRendezVous[] = $type;
        }

        return $this;
    }

    public function removeTypeRendezVous(string $type): self
    {
        if (($key = array_search($type, $this->typesRendezVous)) !== false) {
            unset($this->typesRendezVous[$key]);
        }

        return $this;
    }

    public function hasTypeRendezVous(string $type): bool
    {
        return in_array($type, $this->typesRendezVous);
    }

    public function getRendezVous(): Collection
    {
        return $this->rendezVous;
    }

    public function addRendezVous(RendezVous $rendezVous): self
    {
        if (!$this->rendezVous->contains($rendezVous)) {
            $this->rendezVous[] = $rendezVous;
            $rendezVous->setMedecin($this);
        }

        return $this;
    }

    public function removeRendezVous(RendezVous $rendezVous): self
    {
        if ($this->rendezVous->removeElement($rendezVous)) {
            // Définir le côté propriétaire à null (si nécessaire)
            if ($rendezVous->getMedecin() === $this) {
                $rendezVous->setMedecin(null);
            }
        }

        return $this;
    }
}