<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Inscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InscriptionController extends AbstractController
{
    #[Route('/inscription/event/{id}', name: 'inscription_event', methods: ['POST'])]
    public function inscrire(Event $event, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Vous devez être connecté pour vous inscrire.'], Response::HTTP_UNAUTHORIZED);
        }

        // ✅ Vérifier si l'utilisateur est déjà inscrit à cet événement
        $existingInscription = $entityManager->getRepository(Inscription::class)->findOneBy([
            'user' => $user,
            'event' => $event
        ]);

        if ($existingInscription) {
            return new JsonResponse(['success' => false, 'message' => 'Vous êtes déjà inscrit à cet événement.'], Response::HTTP_BAD_REQUEST);
        }

        // ✅ Vérifier s'il reste des places
        if ($event->getPlacesDisponibles() <= 0) {
            return new JsonResponse(['success' => false, 'message' => 'Plus de places disponibles.'], Response::HTTP_BAD_REQUEST);
        }

        // ✅ Création de l'inscription
        $inscription = new Inscription();
        $inscription->setUser($user);
        $inscription->setEvent($event);

        $entityManager->persist($inscription);
        $event->decrementPlaces();
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Inscription réussie !']);
    }

    #[Route('/inscription/event/{id}/desinscription', name: 'desinscription_event', methods: ['POST'])]
    public function desinscrire(Event $event, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Vous devez être connecté pour vous désinscrire.'], Response::HTTP_UNAUTHORIZED);
        }

        // ✅ Vérifier si l'utilisateur est inscrit à cet événement
        $inscription = $entityManager->getRepository(Inscription::class)->findOneBy([
            'user' => $user,
            'event' => $event
        ]);

        if (!$inscription) {
            return new JsonResponse(['success' => false, 'message' => 'Vous n\'êtes pas inscrit à cet événement.'], Response::HTTP_BAD_REQUEST);
        }

        // ✅ Supprimer l'inscription et libérer une place
        $entityManager->remove($inscription);
        $event->incrementPlaces();
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Désinscription réussie !']);
    }
}
