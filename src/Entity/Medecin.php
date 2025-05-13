<?php


// src/Entity/Medecin.php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Medecin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'medecin', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'string', length: 255)] // Changer de json à string
    private $typesRendezVous = ''; // Stocke les IDs sous forme de chaîne (ex: "1,2,3")

    #[ORM\OneToMany(mappedBy: 'medecin', targetEntity: RendezVous::class)]
    private $rendezVous;

    #[ORM\OneToMany(mappedBy: 'medecin', targetEntity: Notification::class)]
    private Collection $notifications;

    public function __construct()
    {
        $this->rendezVous = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

   
    public function getRendezVous(): Collection
    {
        return $this->rendezVous;
    }




    public function getTypesRendezVous(): array
    {
        return array_map('intval', explode(',', $this->typesRendezVous)); // Convertir en tableau d'entiers
    }

    public function setTypesRendezVous(array $typesRendezVous): self
    {
        $this->typesRendezVous = implode(',', $typesRendezVous); // Convertir le tableau en chaîne
        return $this;
    }

    public function addTypeRendezVous(int $typeId): self
    {
        $types = $this->getTypesRendezVous();
        if (!in_array($typeId, $types)) {
            $types[] = $typeId;
            $this->setTypesRendezVous($types);
        }
        return $this;
    }

    public function removeTypeRendezVous(int $typeId): self
    {
        $types = $this->getTypesRendezVous();
        if (($key = array_search($typeId, $types)) !== false) {
            unset($types[$key]);
            $this->setTypesRendezVous($types);
        }
        return $this;
    }

    public function hasTypeRendezVous(int $typeId): bool
    {
        return in_array($typeId, $this->getTypesRendezVous());
    }

    public function addRendezVous(RendezVous $rendezVous): self
    {
        if (!$this->rendezVous->contains($rendezVous)) {
            $this->rendezVous[] = $rendezVous;
            $rendezVous->setMedecin($this);
        }

        return $this;
    }

    public function removeRendezVous(RendezVous $rendezVous): self
    {
        if ($this->rendezVous->removeElement($rendezVous)) {
            // Définir le côté propriétaire à null (si nécessaire)
            if ($rendezVous->getMedecin() === $this) {
                $rendezVous->setMedecin(null);
            }
        }

        return $this;
    }


    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setMedecin($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getMedecin() === $this) {
                $notification->setMedecin(null);
            }
        }

        return $this;
    }


    public function __toString(): string
{
    return $this->user ? $this->user->getFirstname() . ' ' . $this->user->getLastname() : 'Médecin sans utilisateur';
}

}