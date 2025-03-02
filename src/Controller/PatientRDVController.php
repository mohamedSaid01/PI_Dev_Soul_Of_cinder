<?php

// src/Controller/PatientController.php
namespace App\Controller;
use App\Service\NotificationService;

use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\RendezVous;
use App\Form\RendezVousType;
use App\Repository\MedecinRepository;
use App\Repository\UserRepository;
use App\Repository\RendezVousRepository;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\EtatRendezVousRepository;

class PatientRDVController extends AbstractController
{
    private $notificationService;
    private $security;

    public function __construct(NotificationService $notificationService, Security $security)
    {
        $this->notificationService = $notificationService;
        $this->security = $security;
    }

    #[Route('/ListeMedecins', name: 'liste_medecins')]

    public function listeMedecins(Request $request, MedecinRepository $medecinRepository): Response
    {
      
        $medecins = $medecinRepository->findAll();
        $typeRendezVous = $request->query->get('typeRendezVous', '');
        $medecins = $medecinRepository->findAll();
        return $this->render('patientRDV/liste_medecins.html.twig', [
            'medecins' => $medecins,
            'typeRendezVous' => $typeRendezVous, // Passer la variable à Twig

        ]);
    }

    #[Route('/prendre-rendez-vous/{id}', name: 'prendre_rendez_vous')]
    public function prendreRendezVous(
        Request $request,
        int $id,
        MedecinRepository $medecinRepository,
        Security $security,
        EntityManagerInterface $entityManager,
        RendezVousRepository $rendezVousRepository,
        EtatRendezVousRepository $etatRendezVousRepository
    ): Response {
        // Récupérer le médecin
        $medecin = $medecinRepository->find($id);
        if (!$medecin) {
            throw $this->createNotFoundException('Médecin introuvable.');
        }
    
        // Récupérer le patient connecté
        $user = $security->getUser();
        if (!$user || !$user->getPatient()) {
            throw $this->createAccessDeniedException('Vous devez être un patient pour prendre un rendez-vous.');
        }
        $patient = $user->getPatient();
    
        // Créer un nouveau rendez-vous
        $rendezVous = new RendezVous();
        $rendezVous->setMedecin($medecin);
        $rendezVous->setPatient($patient); // 💡 Associer le patient connecté directement
    
        // Créer le formulaire avec les paramètres nécessaires
        $form = $this->createForm(RendezVousType::class, $rendezVous, [
            'medecin' => $medecin,
            'etat_choices' => $this->filterEtatChoicesByMedecin($medecin, $etatRendezVousRepository),
        ]);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Vérifier la disponibilité du médecin
                $this->verifierDisponibiliteMedecin($medecin, $rendezVous->getDate(), $rendezVous->getHeure(), $rendezVousRepository);
    
                // Enregistrer le rendez-vous
                $entityManager->persist($rendezVous);
                $entityManager->flush();
    
                $this->addFlash('success', 'Votre rendez-vous a été pris avec succès.');
                return $this->redirectToRoute('liste_medecins');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }
    
        return $this->render('patientRDV/prendre_rendez_vous.html.twig', [
            'form' => $form->createView(),
            'medecin' => $medecin,
        ]);
    }
    private function verifierDisponibiliteMedecin(Medecin $medecin, \DateTimeInterface $date, \DateTimeInterface $heure, RendezVousRepository $rendezVousRepository): void
    {
        // Vérifier si le médecin a déjà un rendez-vous à cette date et heure
        $existingRendezVous = $rendezVousRepository->findOneBy([
            'medecin' => $medecin,
            'date' => $date,
            'heure' => $heure,
        ]);
    
        if ($existingRendezVous) {
            throw new \Exception('Ce créneau horaire est déjà occupé par ce médecin.');
        }
    
        // Vérifier que la date et l'heure sont dans le futur
        $dateHeure = new \DateTime($date->format('Y-m-d') . ' ' . $heure->format('H:i:s'));
        if ($dateHeure <= new \DateTime('now')) {
            throw new \Exception('Sélectionner une heure disponible.');
        }
    }


    private function filterEtatChoicesByMedecin(Medecin $medecin, EtatRendezVousRepository $etatRendezVousRepository): array
{
    // Récupérer tous les états possibles
    $allEtats = $etatRendezVousRepository->findAll();

    // Filtrer les états selon les préférences du médecin
    $filteredEtats = array_filter($allEtats, function ($etat) use ($medecin) {
        return in_array($etat->getLibelle(), $medecin->getTypesRendezVous());
    });

    return $filteredEtats;
}



    #[Route('/mes-consultations', name: 'mes_consultations')]
    public function mesConsultations(
        Request $request,
        PatientRepository $patientRepository,
        RendezVousRepository $rendezVousRepository,
       Security $security
    ): Response {
       
           // Récupérer l'utilisateur connecté
    $user = $security->getUser();
    if (!$user || !$user->getPatient()) {
        throw new \LogicException('Utilisateur non reconnu comme patient.');
    }
    
    // Récupérer le patient
    $patient = $user->getPatient();
    // Vérifier si l'utilisateur est bien un patient
  


        // Récupérer les rendez-vous confirmés pour ce patient
        $rendezVous = $rendezVousRepository->findConfirmedRendezVousByPatient($patient);

        return $this->render('patientRDV/mes_consultations.html.twig', [
            'rendezVous' => $rendezVous,
            'patient' => $patient,
        ]);
    }



    /////////////////////////////////////////////////////////////////////////////////////
    #[Route('/lienmeet', name: 'lienmeet')]
public function lienMeet(
    Security $security,
    RendezVousRepository $rendezVousRepository
): Response {
    // Récupérer l'utilisateur connecté
    $user = $security->getUser();
   

    // Récupérer le patient
    $patient = $user->getPatient();

    // Récupérer les rendez-vous acceptés pour ce patient
    $rendezVous = $rendezVousRepository->findAccepted($patient);

    return $this->render('patientRDV/lienmeet.html.twig', [
        'rendezVous' => $rendezVous,
        'patient' => $patient,
    ]);
}

#[Route('/rendez-vous/{id}', name: 'rendez_vous_show', methods: ['GET'])]
public function show(RendezVous $rendezVous): Response
{
    return $this->render('patientRDV/show.html.twig', [
        'rendezVous' => $rendezVous,
    ]);
}

/////////////////////////////////////////////nvl pr
#[Route('/patient/notifications', name: 'patient_notifications')]
public function notifications(): Response
{
    $user = $this->security->getUser();
    $notifications = [];

    if ($user) {
        if ($user->getPatient()) {
            $notifications = $this->notificationService->getUnreadNotificationsForPatient($user->getPatient());
        } elseif ($user->getMedecin()) {
            $notifications = $this->notificationService->getUnreadNotificationsForMedecin($user->getMedecin());
        }
    }

    return $this->render('patientRDV/notifications.html.twig', [
        'notifications' => $notifications, // Assurez-vous de passer cette variable
        'unreadNotificationsCount' => count($notifications), // Compte des notifications non lues
    ]);
}
    #[Route('/patient/notifications/mark-read/{id}', name: 'mark_notification_read')]
    public function markAsRead(int $id, NotificationService $notificationService): Response
    {
        $notification = $notificationService->getNotificationById($id);
        if ($notification) {
            $notificationService->markAsRead($notification);
        }
    
        return new Response(null, 204); // Retourne une réponse vide avec un code 204 (No Content)
    }



}


