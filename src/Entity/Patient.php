<?php

// src/Entity/Patient.php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'patient', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\OneToMany(mappedBy: 'patient', targetEntity: RendezVous::class)]
    private $rendezVous;

<<<<<<< Updated upstream
    public function __construct()
    {
        $this->rendezVous = new ArrayCollection();
=======
    #[ORM\OneToMany(mappedBy: 'patient', targetEntity: Notification::class)]
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

    public function addRendezVous(RendezVous $rendezVous): self
    {
        if (!$this->rendezVous->contains($rendezVous)) {
            $this->rendezVous[] = $rendezVous;
            $rendezVous->setPatient($this);
        }

        return $this;
    }

    public function removeRendezVous(RendezVous $rendezVous): self
    {
        if ($this->rendezVous->removeElement($rendezVous)) {
            // Définir le côté propriétaire à null (si nécessaire)
            if ($rendezVous->getPatient() === $this) {
                $rendezVous->setPatient(null);
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
            $notification->setPatient($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getPatient() === $this) {
                $notification->setPatient(null);
            }
        }

        return $this;
    }
>>>>>>> Stashed changes
}
