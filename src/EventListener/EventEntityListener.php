<?php

namespace App\EventListener;

use App\Entity\Event;
use App\Service\ImageSynchronizer;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

/**
 * Écoute les événements Doctrine pour les entités Event
 * et déclenche la synchronisation des images après chaque opération.
 */
#[AsEntityListener(event: Events::postPersist, entity: Event::class)]
#[AsEntityListener(event: Events::postUpdate, entity: Event::class)]
#[AsEntityListener(event: Events::postRemove, entity: Event::class)]
class EventEntityListener
{
    private $imageSynchronizer;
    
    public function __construct(ImageSynchronizer $imageSynchronizer)
    {
        $this->imageSynchronizer = $imageSynchronizer;
    }
    
    /**
     * Après la création d'un Event
     */
    public function postPersist(Event $event, PostPersistEventArgs $args): void
    {
        $this->syncImagesIfNeeded($event);
    }
    
    /**
     * Après la mise à jour d'un Event
     */
    public function postUpdate(Event $event, PostUpdateEventArgs $args): void
    {
        $this->syncImagesIfNeeded($event);
    }
    
    /**
     * Après la suppression d'un Event
     */
    public function postRemove(Event $event, PostRemoveEventArgs $args): void
    {
        $this->syncImagesIfNeeded($event);
    }
    
    /**
     * Synchronise les images si l'événement a une affiche
     */
    private function syncImagesIfNeeded(Event $event): void
    {
        if ($event->getAffiche()) {
            $this->imageSynchronizer->syncImages();
        }
    }
} 