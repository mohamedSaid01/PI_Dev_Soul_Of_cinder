<?php
// src/Controller/BackController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\EventRepository;
use App\Repository\InscriptionRepository;

final class BackController extends AbstractController
{
    #[Route('/back', name: 'display_dashboard')]
    public function indexAdmin(EventRepository $eventRepository, InscriptionRepository $inscriptionRepository): Response
    {
        $eventsByMonth = $eventRepository->countEventsByMonth();
        $inscriptionsByEvent = $inscriptionRepository->countInscriptionsByEvent();

        // Récupérer les titres des événements
        $eventTitles = $eventRepository->findAllTitles();

        return $this->render('back/index.html.twig', [
            'eventsByMonth' => $eventsByMonth,
            'inscriptionsByEvent' => $inscriptionsByEvent,
            'eventTitles' => $eventTitles,
        ]);
    }
}

