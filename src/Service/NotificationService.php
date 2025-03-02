<?php
// src/Service/NotificationService.php
namespace App\Service;

use App\Entity\Notification;
use App\Entity\Patient;
use App\Entity\Medecin;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Crée une notification pour un patient.
     */
    public function createNotificationForPatient(Patient $patient, string $message): void
    {
        $notification = new Notification();
        $notification->setMessage($message);
        $notification->setIsRead(false);
        $notification->setPatient($patient);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    /**
     * Crée une notification pour un médecin.
     */
    public function createNotificationForMedecin(Medecin $medecin, string $message): void
    {
        $notification = new Notification();
        $notification->setMessage($message);
        $notification->setIsRead(false);
        $notification->setMedecin($medecin);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    /**
     * Récupère les notifications non lues pour un patient.
     */
    public function getUnreadNotificationsForPatient(Patient $patient): array
    {
        return $this->entityManager->getRepository(Notification::class)->findBy([
            'patient' => $patient,
            'isRead' => false,
        ]);
    }

    /**
     * Récupère les notifications non lues pour un médecin.
     */
    public function getUnreadNotificationsForMedecin(Medecin $medecin): array
    {
        return $this->entityManager->getRepository(Notification::class)->findBy([
            'medecin' => $medecin,
            'isRead' => false,
        ]);
    }

    /**
     * Récupère une notification par son ID.
     */
    public function getNotificationById(int $id): ?Notification
    {
        return $this->entityManager->getRepository(Notification::class)->find($id);
    }

    /**
     * Marque une notification comme lue.
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->setIsRead(true);
        $this->entityManager->flush();
    }
}
