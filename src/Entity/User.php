<?php

// src/Entity/User.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
<<<<<<< Updated upstream
=======
use App\Enum\Gender;
use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\Specialite;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
>>>>>>> Stashed changes

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

<<<<<<< Updated upstream
=======
    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\NotBlank(message: 'Veuillez entrer votre numéro de licence.', groups: ['RegistrationMedecin'])]
    #[Assert\Length(
        max: 50,
        maxMessage: 'Le numéro de licence ne peut pas dépasser {{ limit }} caractères.',
        groups: ['RegistrationMedecin']
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z]{3}\d{5}$/',
        message: 'Le numéro de licence doit être au format ABC12345 (3 lettres suivies de 5 chiffres).',
        groups: ['RegistrationMedecin']
    )]
    private ?string $numeroLicence = null;

    #[ORM\Column(type: 'integer', nullable: true)]
#[Assert\NotBlank(message: 'Veuillez entrer votre âge.', groups: ['RegistrationUser', 'RegistrationMedecin'])]
#[Assert\Range(
    min: 1,
    max: 120,
    notInRangeMessage: 'L\'âge doit être compris entre {{ min }} et {{ max }}.',
    groups: ['RegistrationUser', 'RegistrationMedecin']
)]
private ?int $age = null;


    #[ORM\Column(enumType: Specialite::class, nullable: true)]
    #[Assert\NotBlank(message: 'Veuillez sélectionner une spécialité.', groups: ['RegistrationMedecin'])]
    private ?Specialite $specialite = null;


    private ?string $currentPassword = null;
private ?string $newPassword = null;
private ?string $confirmPassword = null;

#[ORM\Column(type: 'string', nullable: true)]
private $medicalFile;

#[ORM\Column(type: 'boolean')]
private $isVerified = false;

#[ORM\Column(type: 'string', length:255 ,nullable: true)]
private $verificationToken;
    
    
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
}
=======

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    // Ajoute le getter et le setter
public function getNumeroLicence(): ?string
{
    return $this->numeroLicence;
}

public function setNumeroLicence(?string $numeroLicence): static
{
    $this->numeroLicence = $numeroLicence;

    return $this;
}

public function getSpecialite(): ?Specialite
{
    return $this->specialite;
}

public function setSpecialite(?Specialite $specialite): static
{
    $this->specialite = $specialite;

    return $this;
}

public function getAge(): ?int
{
    return $this->age;
}

public function setAge(?int $age): static
{
    $this->age = $age;

    return $this;
}

public function getCurrentPassword(): ?string
{
    return $this->currentPassword;
}

public function setCurrentPassword(?string $currentPassword): static
{
    $this->currentPassword = $currentPassword;
    return $this;
}

public function getNewPassword(): ?string
{
    return $this->newPassword;
}

public function setNewPassword(?string $newPassword): static
{
    $this->newPassword = $newPassword;
    return $this;
}

public function getConfirmPassword(): ?string
{
    return $this->confirmPassword;
}

public function setConfirmPassword(?string $confirmPassword): static
{
    $this->confirmPassword = $confirmPassword;
    return $this;
}

public function getMedicalFile(): ?string
{
    return $this->medicalFile;
}

public function setMedicalFile(?string $medicalFile): self
{
    $this->medicalFile = $medicalFile;

    return $this;
}


public function getIsVerified(): bool
{
    return $this->isVerified;
}

public function setIsVerified(bool $isVerified): self
{
    $this->isVerified = $isVerified;
    return $this;
}

public function getVerificationToken(): ?string
{
    return $this->verificationToken;
}

public function setVerificationToken(?string $verificationToken): self
{
    $this->verificationToken = $verificationToken;
    return $this;
}



#[ORM\OneToMany(mappedBy: 'user', targetEntity: Inscription::class, cascade: ['remove'])]
    private Collection $inscriptions;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
    }
    /**
     * @return Collection<int, Inscription>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): self
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setUser($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): self
    {
        if ($this->inscriptions->removeElement($inscription)) {
            if ($inscription->getUser() === $this) {
                $inscription->setUser(null);
            }
        }

        return $this;
    }


    



    #[ORM\OneToOne(targetEntity: Medecin::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private $medecin;

    #[ORM\OneToOne(targetEntity: Patient::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private $patient;
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
>>>>>>> Stashed changes
