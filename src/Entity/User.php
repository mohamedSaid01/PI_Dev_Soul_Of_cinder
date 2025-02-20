<?php

// src/Entity/User.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\Column(type: 'string', length: 255)]
    private $nom;

    #[ORM\Column(type: 'string', length: 255)]
    private $prenom;

    #[ORM\Column(type: 'string', length: 10)]
    private $role; // 'med' ou 'pat'

    #[ORM\OneToOne(targetEntity: Medecin::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private $medecin;

    #[ORM\OneToOne(targetEntity: Patient::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private $patient;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_' . strtoupper($this->role);
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function eraseCredentials()
    {
        // No-op
    }

       // Méthode pour récupérer le Patient associé
       public function getPatient(): ?Patient
       {
           return $this->patient;
       }
   
       // Méthode pour associer un Patient à cet Utilisateur
       public function setPatient(?Patient $patient): self
       {
           $this->patient = $patient;
   
           if ($patient !== null) {
               $patient->setUser($this); // Synchroniser la relation inverse
           }
   
           return $this;
       }

           // Méthode pour récupérer le Médecin associé
    public function getMedecin(): ?Medecin
    {
        return $this->medecin;
    }

    // Méthode pour associer un Médecin à cet Utilisateur
    public function setMedecin(?Medecin $medecin): self
    {
        $this->medecin = $medecin;

        if ($medecin !== null) {
            $medecin->setUser($this); // Synchroniser la relation inverse
        }

        return $this;
    }
}