<?php
// src/Entity/RendezVous.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'rendezVous')]
    private $user;

    #[ORM\Column(type: 'datetime')]
    private $dateHeure;

    #[ORM\ManyToOne(targetEntity: EtatRendezVous::class)]
    private $etat;
    #[ORM\Column(type: 'boolean')]
    private $statut;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

public function getStatut(): ?bool
{
    return $this->statut;
}

public function setStatut(bool $statut): self
{
    $this->statut = $statut;

    return $this;
}
    public function getDateHeure(): ?\DateTimeInterface
    {
        return $this->dateHeure;
    }

    public function setDateHeure(\DateTimeInterface $dateHeure): self
    {
        $this->dateHeure = $dateHeure;

        return $this;
    }

    public function getEtat(): ?EtatRendezVous
    {
        return $this->etat;
    }

    public function setEtat(?EtatRendezVous $etat): self
    {
        $this->etat = $etat;

        return $this;
    }
}