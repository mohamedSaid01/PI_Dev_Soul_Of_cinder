<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\RendezVous;
use App\Entity\Medecin;
use Doctrine\ORM\EntityManagerInterface;

class CalendarController extends AbstractController
{
    #[Route('/medecin/calendar', name: 'medecin_calendar')]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupérer l'utilisateur connecté (médecin)
        $user = $this->getUser();
        $medecin = $user->getMedecin();

        // Récupérer les rendez-vous du médecin
        $rendezVous = $em->getRepository(RendezVous::class)->findBy(['medecin' => $medecin]);

        // Formater les rendez-vous pour FullCalendar
        $events = [];
        foreach ($rendezVous as $rdv) {
            $events[] = [
                'title' => $rdv->getPatient()->getUser()->getFirstName() . ' : ' . 
                          $rdv->getHeure()->format('H:i'), // Affiche uniquement l'heure
                'start' => $rdv->getDate()->format('Y-m-d') . 'T' . $rdv->getHeure()->format('H:i:s'),
            ];
            
            
            
        }

        return $this->render('calendar/index.html.twig', [
            'events' => json_encode($events),
        ]);
    }
}