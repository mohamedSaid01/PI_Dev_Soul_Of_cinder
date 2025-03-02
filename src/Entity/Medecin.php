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

<<<<<<< Updated upstream
    #[ORM\Column(type: 'string', length: 255)]
    private $specialite;

    #[ORM\Column(type: 'string', length: 20)]
    private $telephone;

=======
>>>>>>> Stashed changes
    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'medecin', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'json')]
    private $typesRendezVous = []; // Types acceptés : "enligne", "presentiel", "hybride"

    #[ORM\OneToMany(mappedBy: 'medecin', targetEntity: RendezVous::class)]
    private $rendezVous;

<<<<<<< Updated upstream
    public function __construct()
    {
        $this->rendezVous = new ArrayCollection();
=======
    #[ORM\OneToMany(mappedBy: 'medecin', targetEntity: Notification::class)]
    private Collection $notifications;

    public function __construct()
    {
        $this->rendezVous = new ArrayCollection();
        $this->notifications = new ArrayCollection();

>>>>>>> Stashed changes
    }

    public function getId(): ?int
    {
        return $this->id;
    }

<<<<<<< Updated upstream
    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): self
    {
        $this->specialite = $specialite;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

=======
>>>>>>> Stashed changes
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTypesRendezVous(): array
    {
        return $this->typesRendezVous;
    }

    public function setTypesRendezVous(array $typesRendezVous): self
    {
        $this->typesRendezVous = $typesRendezVous;

        return $this;
    }

    public function addTypeRendezVous(string $type): self
    {
        if (!in_array($type, $this->typesRendezVous)) {
            $this->typesRendezVous[] = $type;
        }

        return $this;
    }

    public function removeTypeRendezVous(string $type): self
    {
        if (($key = array_search($type, $this->typesRendezVous)) !== false) {
            unset($this->typesRendezVous[$key]);
        }

        return $this;
    }

    public function hasTypeRendezVous(string $type): bool
    {
        return in_array($type, $this->typesRendezVous);
    }

    public function getRendezVous(): Collection
    {
        return $this->rendezVous;
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
<<<<<<< Updated upstream
=======




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
>>>>>>> Stashed changes
}