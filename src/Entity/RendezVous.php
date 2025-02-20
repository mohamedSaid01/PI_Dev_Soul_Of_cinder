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

    #[ORM\ManyToOne(targetEntity: Medecin::class, inversedBy: 'rendezVous')]
    private $medecin;

    #[ORM\ManyToOne(targetEntity: Patient::class, inversedBy: 'rendezVous')]
    private $patient;
    #[ORM\Column(type: 'date')]
    private $date;

    #[ORM\Column(type: 'time')]
    private $heure;

#[ORM\ManyToOne(targetEntity: EtatRendezVous::class)]
private $etat;
#[ORM\Column(type: 'boolean')]
private $statut=false;
#[ORM\Column(type: 'boolean')]
private $annule = false; // Valeur par défaut à false (non annulé)
public function getDate(): ?\DateTimeInterface
{
    return $this->date;
}

public function setDate(\DateTimeInterface $date): self
{
    $this->date = $date;
    return $this;
}

public function getHeure(): ?\DateTimeInterface
{
    return $this->heure;
}

public function setHeure(\DateTimeInterface $heure): self
{
    $this->heure = $heure;
    return $this;
}

// Méthode pour combiner la date et l'heure en un objet DateTime

public function isAnnule(): bool
{
    return $this->annule;
}

public function setAnnule(bool $annule): self
{
    $this->annule = $annule;

    return $this;
}
   
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMedecin(): ?Medecin
    {
        return $this->medecin;
    }

    public function setMedecin(?Medecin $medecin): self
    {
        $this->medecin = $medecin;

        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;

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
