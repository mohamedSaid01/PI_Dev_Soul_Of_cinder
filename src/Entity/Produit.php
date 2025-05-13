<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du produit est obligatoire.")]
    #[Assert\Type(type: 'string', message: "Le nom doit être une chaîne de caractères.")]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z]/',
        message: "Le nom ne peut pas commencer par un chiffre."
    )]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $name = null;


   #[ORM\Column(length: 1000)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Type(type: 'string', message: "La description doit être une chaîne de caractères.")]
    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: "La description doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $desciption = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le prix est obligatoire.")]
    #[Assert\Type(type: 'float', message: "Le prix doit être un nombre.")]
    #[Assert\Range(
        min: 0.50,
        max: 1000,
        notInRangeMessage: "Le prix doit être compris entre {{ min }} DT et {{ max }} DT."
    )]
    private ?float $price = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La quantité est obligatoire.")]
    #[Assert\Type(type: 'integer', message: "La quantité doit être un entier.")]
    #[Assert\Positive(message: "La quantité doit être un nombre positif.")]
    private ?int $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?Category $Category = null;

    /**
     * @var Collection<int, CommandeLigne>
     */
    #[ORM\OneToMany(targetEntity: CommandeLigne::class, mappedBy: 'produit')]
    private Collection $commandeLignes;

    /**
     * @var Collection<int, Favori>
     */
    #[ORM\OneToMany(targetEntity: Favori::class, mappedBy: 'produit')]
    private Collection $favoris;

 /**
     * @var Collection<int, Commmentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'produit', orphanRemoval: true)]
private Collection $commentaires;



    public function __construct()
    {
        $this->commandeLignes = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->commentaires = new ArrayCollection();

    }

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

    public function getDesciption(): ?string
    {
        return $this->desciption;
    }

    public function setDesciption(string $desciption): static
    {
        $this->desciption = $desciption;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
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
    
    public function getCategory(): ?Category
    {
        return $this->Category;
    }

    public function setCategory(?Category $Category): static
    {
        $this->Category = $Category;

        return $this;
    }

    /**
     * @return Collection<int, CommandeLigne>
     */
    public function getCommandeLignes(): Collection
    {
        return $this->commandeLignes;
    }

    public function addCommandeLigne(CommandeLigne $commandeLigne): static
{
    if (!$this->commandeLignes->contains($commandeLigne)) {
        $this->commandeLignes->add($commandeLigne);
        $commandeLigne->setProduit($this);
    }
    return $this;
}

public function removeCommandeLigne(CommandeLigne $commandeLigne): static
{
    if ($this->commandeLignes->removeElement($commandeLigne)) {
        if ($commandeLigne->getProduit() === $this) {
            $commandeLigne->setProduit(null);
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
            $favori->setProduit($this);
        }

        return $this;
    }

    public function removeFavori(Favori $favori): static
    {
        if ($this->favoris->removeElement($favori)) {
            // set the owning side to null (unless already changed)
            if ($favori->getProduit() === $this) {
                $favori->setProduit(null);
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
        $commentaire->setProduit($this);
    }

    return $this;
}

public function removeCommentaire(Commentaire $commentaire): self
{
    if ($this->commentaires->removeElement($commentaire)) {
        // set the owning side to null (unless already changed)
        if ($commentaire->getProduit() === $this) {
            $commentaire->setProduit(null);
        }
    }

    return $this;
}


}