<?php

namespace App\Entity;

use App\Repository\TypeReclamationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeReclamationRepository::class)]
class TypeReclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type_reclamation = null;

    #[ORM\OneToMany(mappedBy: 'typeReclamation', targetEntity: Reclamation::class, orphanRemoval: false)]
    private Collection $reclamations;

    /**
     * @var Collection<int, Reponse>
     */
    #[ORM\OneToMany(targetEntity: Reponse::class, mappedBy: 'type_recalmation')]
    private Collection $reponses;
    
    public function __construct()
    {
        $this->reclamations = new ArrayCollection();
        $this->reponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeReclamation(): ?string
    {
        return $this->type_reclamation;
    }

    public function setTypeReclamation(string $type_reclamation): static
    {
        $this->type_reclamation = $type_reclamation;
        return $this;
    }

    /**
     * @return Collection<int, Reclamation>
     */
    public function getReclamations(): Collection
    {
        return $this->reclamations;
    }

    public function addReclamation(Reclamation $reclamation): static
    {
        if (!$this->reclamations->contains($reclamation)) {
            $this->reclamations->add($reclamation);
            $reclamation->setTypeReclamation($this);
        }
        return $this;
    }

    public function removeReclamation(Reclamation $reclamation): static
    {
        if ($this->reclamations->removeElement($reclamation)) {
            if ($reclamation->getTypeReclamation() === $this) {
                $reclamation->setTypeReclamation(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Reponse>
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): static
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setTypeRecalmation($this);
        }

        return $this;
    }

    public function removeReponse(Reponse $reponse): static
    {
        if ($this->reponses->removeElement($reponse)) {
            // set the owning side to null (unless already changed)
            if ($reponse->getTypeRecalmation() === $this) {
                $reponse->setTypeRecalmation(null);
            }
        }

        return $this;
    }
}
