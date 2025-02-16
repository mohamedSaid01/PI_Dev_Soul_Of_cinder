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

}
