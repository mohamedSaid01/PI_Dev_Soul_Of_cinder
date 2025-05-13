<?php

namespace App\Entity;

use App\Repository\CommandeLigneRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeLigneRepository::class)]
class CommandeLigne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commandeLignes')]
    private ?Commande $commande = null;

    #[ORM\ManyToOne(inversedBy: 'commandeLignes')]
    private ?Produit $produit = null;

    #[ORM\Column]
    private ?int $quantity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
{
    // Synchroniser la relation bidirectionnelle
    if ($this->produit !== null && $this->produit !== $produit) {
        $this->produit->removeCommandeLigne($this);
    }

    $this->produit = $produit;

    if ($produit !== null && $produit !== $this->produit) {
        $produit->addCommandeLigne($this);
    }

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
}
