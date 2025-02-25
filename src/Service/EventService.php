<?php
namespace App\Service;

use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class EventService
{
    private EventRepository $eventRepository;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(EventRepository $eventRepository, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Archiver automatiquement les événements expirés.
     */
    public function archiveExpiredEvents(): void
    {
        $expiredEvents = $this->eventRepository->findExpiredEvents();

        if (empty($expiredEvents)) {
            $this->logger->info('Aucun événement à archiver.');
            return;
        }

        foreach ($expiredEvents as $event) {
            $event->setIsArchived(true); // Marquer l'événement comme archivé
            $this->logger->info('Événement archivé : ' . $event->getTitle());
        }

        $this->entityManager->flush();
        $this->logger->info('Archivage terminé.');
    }
}
