<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Enum\Gender;
use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\Specialite;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['numeroLicence'], message: 'Ce numéro de licence est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Veuillez entrer un email.', groups: ['RegistrationUser', 'RegistrationMedecin'])]
    #[Assert\Email(message: 'L\'email doit être valide.', groups: ['RegistrationUser', 'RegistrationMedecin'])]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
        message: 'L\'email doit appartenir à gmail.com.',
        groups: ['RegistrationUser', 'RegistrationMedecin']
    )]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    // #[Assert\NotBlank(message: 'Veuillez entrer votre mot de passe.', groups: ['RegistrationUser', 'RegistrationMedecin'])]
    // #[Assert\Length(
    //     min: 8,
    //     minMessage: "Le mot de passe doit contenir au moins {{ limit }} caractères.",
    //     max: 4096,
    //     groups: ['RegistrationUser', 'RegistrationMedecin']
    // )]
    // #[Assert\Regex(
    //     pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\d\s]).{8,}$/",
    //     message: "Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, un chiffre et un caractère spécial.",
    //     groups: ['RegistrationUser', 'RegistrationMedecin']
    // )]
    // #[Assert\NotCompromisedPassword(
    //     message: "Ce mot de passe a été compromis dans une fuite de données. Veuillez en choisir un autre.",
    //     groups: ['RegistrationUser', 'RegistrationMedecin']
    // )]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez entrer votre prénom.', groups: ['RegistrationUser', 'RegistrationMedecin'])]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z\s]+$/',
        message: 'Le prénom ne doit contenir que des lettres.',
        groups: ['RegistrationUser', 'RegistrationMedecin']
    )]
    #[Assert\Length(
        max: 15,
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères.',
        groups: ['RegistrationUser', 'RegistrationMedecin']
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Veuillez entrer votre nom.', groups: ['RegistrationUser', 'RegistrationMedecin'])]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z\s]+$/', 
        message: 'Le nom ne doit contenir que des lettres.',
        groups: ['RegistrationUser', 'RegistrationMedecin']
    )]
    #[Assert\Length(
        max: 15,
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.',
        groups: ['RegistrationUser', 'RegistrationMedecin']
    )]
    private ?string $lastName = null;

    #[ORM\Column(enumType: Gender::class, nullable: true)]
    #[Assert\NotBlank(message: 'Veuillez sélectionner un genre.', groups: ['RegistrationUser', 'RegistrationMedecin'])]
    private ?Gender $gender = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Veuillez entrer votre adresse.', groups: ['RegistrationUser', 'RegistrationMedecin'])]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: 'L\'adresse doit comporter au moins {{ limit }} caractères.',
        maxMessage: 'L\'adresse ne peut pas dépasser {{ limit }} caractères.',
        groups: ['RegistrationUser', 'RegistrationMedecin']
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9\s,.\'-]+$/',
        message: 'L\'adresse contient des caractères invalides.',
        groups: ['RegistrationUser', 'RegistrationMedecin']
    )]
    private ?string $adress = null;

    #[ORM\Column(length: 8, nullable: true)]
    #[Assert\NotBlank(message: 'Veuillez entrer votre numéro de téléphone.', groups: ['RegistrationUser', 'RegistrationMedecin'])]
    #[Assert\Length(
        min: 8,
        max: 8,
        exactMessage: 'Le numéro de téléphone doit contenir exactement {{ limit }} chiffres.',
        groups: ['RegistrationUser', 'RegistrationMedecin']
    )]
    #[Assert\Regex(
        pattern: '/^[259]\d{7}$/',
        message: 'Le numéro de téléphone doit commencer par 2, 5 ou 9 et contenir uniquement des chiffres.',
        groups: ['RegistrationUser', 'RegistrationMedecin']
    )]
    private ?string $phoneNumber = null;

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


#[ORM\Column(type: 'integer', options: ["default" => 0])]
private int $failedLoginAttempts = 0;

#[ORM\Column(type: 'boolean', options: ["default" => false])]
private bool $isBlocked = false;

#[ORM\Column(type: 'datetime', nullable: true)]
private ?\DateTime $blockedUntil = null;

#[ORM\Column(type: 'string', length: 20)]
private $status = 'non_verifie'; // Par défaut, le statut est "non_verifie"
    
    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $comments;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        return $roles = $this->roles;
        //  guarantee every user at least has ROLE_USER
            $roles[] = 'ROLE_USER';

            return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(?Gender $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): static
    {
        $this->adress = $adress;

        return $this;
    }

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

public function getFailedLoginAttempts(): int
{
    return $this->failedLoginAttempts;
}

public function setFailedLoginAttempts(int $failedLoginAttempts): self
{
    $this->failedLoginAttempts = $failedLoginAttempts;
    return $this;
}

public function getIsBlocked(): bool
{
    return $this->isBlocked;
}

public function setIsBlocked(bool $isBlocked): self
{
    $this->isBlocked = $isBlocked;
    return $this;
}

public function getBlockedUntil(): ?\DateTime
{
    return $this->blockedUntil;
}

public function setBlockedUntil(?\DateTime $blockedUntil): self
{
    $this->blockedUntil = $blockedUntil;
    return $this;
}

public function getStatus(): ?string
{
    return $this->status;
}

public function setStatus(string $status): self
{
    $this->status = $status;

    return $this;
}


#[ORM\OneToMany(mappedBy: 'user', targetEntity: Inscription::class, cascade: ['remove'])]
    private Collection $inscriptions;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
        $this->posts = new ArrayCollection();


        $this->commandes = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->comments = new ArrayCollection();
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

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'author')]
    private Collection $posts;

    /**
    * @var Collection<int, Commande>
     */


    /**
     * @var Collection<int, Favori>
     */
    #[ORM\OneToMany(targetEntity: Favori::class, mappedBy: 'user')]
    private Collection $favoris;

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

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setUser($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getUser() === $this) {
                $commande->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Favori>
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavori(Favori $favori): static
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris->add($favori);
            $favori->setUser($this);
        }

        return $this;
    }

    public function removeFavori(Favori $favori): static
    {
        if ($this->favoris->removeElement($favori)) {
            // set the owning side to null (unless already changed)
            if ($favori->getUser() === $this) {
                $favori->setUser(null);
            }
        }

        return $this;
    }
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }
    
    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setUser($this);
        }
    
        return $this;
    }
    
    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getUser() === $this) {
                $commentaire->setUser(null);
            }
        }
    
        return $this;
    }



     /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }






    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'user')]
private Collection $commandes;
#[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'user', orphanRemoval: true)]
private Collection $commentaires;



}
