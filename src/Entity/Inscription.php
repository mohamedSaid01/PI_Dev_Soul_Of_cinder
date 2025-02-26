<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\InscriptionRepository;

#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "inscriptions")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: "inscriptions")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Event $event = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: "La date d'inscription est obligatoire.")]
    private ?\DateTimeInterface $dateInscription = null;

    #[ORM\Column(type: 'boolean')]
    private bool $hasUnsubscribed = false;

    public function __construct()
    {
        $this->dateInscription = new \DateTime(); // Définit la date actuelle par défaut
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;
        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): self
    {
        $this->dateInscription = $dateInscription;
        return $this;
    }
    public function hasUnsubscribed(): bool
    {
        return $this->hasUnsubscribed;
    }
    public function setHasUnsubscribed(bool $hasUnsubscribed): self
    {
        $this->hasUnsubscribed = $hasUnsubscribed;
        return $this;
    }
}
