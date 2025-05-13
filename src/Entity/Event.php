<?php
// src/Entity/Event.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\Repository\EventRepository;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Inscription;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[Vich\Uploadable]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre ne doit pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le titre ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "La description ne doit pas être vide.")]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: "La date de début ne doit pas être vide.")]
    #[Assert\Type("\DateTimeInterface")]
    #[Assert\GreaterThanOrEqual("today", message: "La date de début ne peut pas être dans le passé.")]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: "La date de fin ne doit pas être vide.")]
    #[Assert\Type("\DateTimeInterface")]
    #[Assert\GreaterThan(propertyPath: "startDate", message: "La date de fin doit être supérieure à la date de début.")]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le lieu ne doit pas être vide.")]
    private ?string $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $affiche = null;

    #[Vich\UploadableField(mapping: 'event_affiche', fileNameProperty: 'affiche')]
    #[Assert\File(
        maxSize: "2M",
        mimeTypes: ["image/jpeg", "image/png"],
        maxSizeMessage: "Le fichier ne doit pas dépasser 2 Mo.",
        mimeTypesMessage: "Seuls les fichiers JPEG et PNG sont autorisés."
    )]
    private ?File $afficheFile = null;


    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: CategorieEvent::class, inversedBy: "events")]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "La catégorie ne doit pas être vide.")]
    private ?CategorieEvent $categorie = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isArchived = false; 

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $longitude = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Inscription::class, cascade: ['remove'])]
    private Collection $inscriptions;
    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: "Le nombre des places ne doit pas être vide.")]
    #[Assert\PositiveOrZero(message: "Le nombre de places doit être positif.")]
    private ?int $placesDisponibles = null;


    public function __construct()
    {
        $this->startDate = new \DateTime(); // Définit la date actuelle par défaut
        $this->endDate = new \DateTime('+1 day'); // Définit la date de fin à demain par défaut
        $this->inscriptions = new ArrayCollection();

    }

    public function isExpired(): bool
    {
        $now = new DateTime(); // Date actuelle
        return $this->startDate < $now; // Vérifie si la date de fin est passée
    }
    
    private array $badWords = ['tunis', 'kebili', 'esprit', 'wael']; // Liste des mots à censurer

    private function filterBadWords(string $text): string
  {
      foreach ($this->badWords as $badWord) {
          $text = str_ireplace($badWord, str_repeat('*', strlen($badWord)), $text);
      }
      return $text;
  }
  // Getters et Setters
  public function getId(): ?int
  {
    return $this->id;
  }

  public function getTitle(): string
  {
    return $this->filterBadWords($this->title ?? ''); // Évite null
  }

  public function setTitle(?string $title): self
{
    $this->title = $title ? $this->filterBadWords($title) : null;
    return $this;
}

  public function getDescription(): string
  {
      return $this->filterBadWords($this->description ?? ''); // Évite null
  }

  public function setDescription(?string $description): self
  {
      $this->description = $description ? $this->filterBadWords($description) : null;
      return $this;
  }

  public function getStartDate(): ?\DateTimeInterface
  {
    return $this->startDate;
  }

  public function setStartDate(\DateTimeInterface $startDate): self
  {
    $this->startDate = $startDate;

    return $this;
  }

  public function getEndDate(): ?\DateTimeInterface
  {
    return $this->endDate;
  }

  public function setEndDate(\DateTimeInterface $endDate): self
  {
    $this->endDate = $endDate;

    return $this;
  }

  public function getLocation(): ?string
  {
    return $this->location;
  }

  public function setLocation(string $location): self
  {
    $this->location = $location;

    return $this;
  }

  public function getAffiche(): ?string
  {
    return $this->affiche;
  }

  public function setAffiche(?string $affiche): self
  {
    $this->affiche = $affiche;

    return $this;
  }

  public function getAfficheFile(): ?File
    {
        return $this->afficheFile;
    }

    public function setAfficheFile(?File $afficheFile = null): self
    {
        $this->afficheFile = $afficheFile;

        if ($afficheFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

  public function isArchived(): bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(bool $isArchived): self
    {
        $this->isArchived = $isArchived;
        return $this;
    }

  public function getUpdatedAt(): ?\DateTimeInterface
  {
    return $this->updatedAt;
  }

  public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
  {
    $this->updatedAt = $updatedAt;

    return $this;
  }

  public function getCategorie(): ?CategorieEvent
  {
    return $this->categorie;
  }

  public function setCategorie(?CategorieEvent $categorie): self
  {
    $this->categorie = $categorie;

    return $this;
  }

  public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
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
            $inscription->setEvent($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): self
    {
        if ($this->inscriptions->removeElement($inscription)) {
            if ($inscription->getEvent() === $this) {
                $inscription->setEvent(null);
            }
        }

        return $this;
    }
    public function getPlacesDisponibles(): ?int
    {
        return $this->placesDisponibles;
    }

    public function setPlacesDisponibles(?int $placesDisponibles): self
    {
        $this->placesDisponibles = $placesDisponibles;
        return $this;
    }

    public function decrementPlaces(): void
    {
        if ($this->placesDisponibles > 0) {
            $this->placesDisponibles--;
        }
    }

    public function incrementPlaces(): void
    {
        $this->placesDisponibles++;
    }
    
}