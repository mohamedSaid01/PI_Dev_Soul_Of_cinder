<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_reclamation = null;

    #[ORM\ManyToOne(targetEntity: Medecin::class)]
    #[ORM\JoinColumn(name: 'medecin_id', referencedColumnName: 'id', nullable: true)]
    private ?Medecin $medecin = null; // Use camelCase for property names

    // Getter and Setter for medecin
    public function getMedecin(): ?Medecin
    {
        return $this->medecin;
    }

    public function setMedecin(?Medecin $medecin): self
    {
        $this->medecin = $medecin;
        return $this;
    }


//     #[ORM\Column(length: 255, nullable: true)] // nullable: true permet NULL
// private ?string  = null;

//    // src/Entity/YourEntity.php

#[ORM\Column(type: 'string', length: 255, nullable: true)] // nullable: true permet NULL
private ?string $photo = null; 


#[ORM\ManyToOne(targetEntity: TypeReclamation::class, inversedBy: 'reclamations')]
    #[ORM\JoinColumn(nullable: true)]
    private ?TypeReclamation $typeReclamation = null;
    public function __construct()
    {
        $this->date_reclamation = new \DateTime(); // Définit la date actuelle par défaut
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateReclamation(): ?\DateTimeInterface
    {
        return $this->date_reclamation;
    }

    public function setDateReclamation(\DateTimeInterface $date_reclamation): static
    {
        $this->date_reclamation = $date_reclamation;
        return $this;
    }
    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    public function getTypeReclamation(): ?TypeReclamation
    {
        return $this->typeReclamation;
    }

    public function setTypeReclamation(?TypeReclamation $typeReclamation): static
    {
        $this->typeReclamation = $typeReclamation;
        return $this;
    }
}
