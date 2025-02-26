<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\NotificationRepository;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $message = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isRead = false;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->isRead = false;
    }

    public function getId(): ?int { return $this->id; }

    public function getMessage(): ?string { return $this->message; }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function isRead(): bool { return $this->isRead; }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
}
