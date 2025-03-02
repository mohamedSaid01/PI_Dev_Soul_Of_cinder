<?php

// src/Controller/MedecinController.php
namespace App\Controller;


use App\Form\RendezVousEditType;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use App\Form\MedecinPreferencesType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MedecinRepository;
use App\Repository\RendezVousRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bundle\SecurityBundle\Security;
use App\Service\NotificationService;
use App\Entity\RendezVous;

class MedecinRDVController extends AbstractController
{

    private EntityManagerInterface $entityManager;
    private MedecinRepository $medecinRepository;
    private RendezVousRepository $rendezVousRepository;
    private $session;
    private NotificationService $notificationService;
    private int $unreadNotificationsCount ; 
    private $requestStack;
    public function __construct(
        EntityManagerInterface $entityManager,
        MedecinRepository $medecinRepository,
        RendezVousRepository $rendezVousRepository,
 
     
        RequestStack $requestStack,
        NotificationService $notificationService,
        

    ) {
        $this->entityManager = $entityManager;
        $this->medecinRepository = $medecinRepository;
        $this->rendezVousRepository = $rendezVousRepository;
        $this->notificationService = $notificationService;
        $this->requestStack = $requestStack;

    }

   

    #[Route('/medecin/rdv', name: 'medecin_liste_rdv')]
    public function listeRendezVous(Security $security): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
        
        // Vérifier que l'utilisateur est bien un médecin
        if (!$user || !$user->getMedecin()) {
            throw $this->createAccessDeniedException('Vous devez être un médecin pour voir cette page.');
        }
    
        $medecin = $user->getMedecin(); 
    
        // Récupérer tous les rendez-vous du médecin connecté
        $rendezVous = $this->rendezVousRepository->findBy(['medecin' => $medecin]);
    
        return $this->render('medecinRDV/liste_rendez_vous.html.twig', [
            'rendezVous' => $rendezVous,
        ]);
    }

    #[Route('/medecin/rdv/demandes', name: 'medecin_rendez_vous_demandes')]
    public function rendezVousDemandes(Security $security): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
        
        // Vérifier que l'utilisateur est bien un médecin
        if (!$user || !$user->getMedecin()) {
            throw $this->createAccessDeniedException('Vous devez être un médecin pour voir cette page.');
        }
    
        $medecin = $user->getMedecin(); // ✅ Récupération du médecin connecté
    
        // Récupérer les rendez-vous en attente pour ce médecin
        $rendezVousDemandes = $this->rendezVousRepository->findBy([
            'medecin' => $medecin,
            'statut' => false, // Seulement les rendez-vous non confirmés
        ]);
    
        return $this->render('medecinRDV/rendez_vous_demandes.html.twig', [
            'rendezVousDemandes' => $rendezVousDemandes,
        ]);
    }

    #[Route('/medecin/rendez-vous/{id}/modifier-date', name: 'modifier_date_rendez_vous', methods: ['GET', 'POST'])]
    public function modifierDateRendezVous(
        Request $request,
        int $id,
        RendezVousRepository $rendezVousRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer le rendez-vous
        $rendezVous = $rendezVousRepository->find($id);
    
        if (!$rendezVous) {
            throw $this->createNotFoundException('Rendez-vous non trouvé.');
        }
    
        // Création du formulaire avec des restrictions sur la date et les créneaux horaires
        // Dans votre méthode modifierDateRendezVous
$form = $this->createFormBuilder($rendezVous)
->add('date', DateType::class, [
    'label' => 'Date',
    'widget' => 'single_text',
    'html5' => true,
    'attr' => [
        'class' => 'form-control',
        'min' => (new \DateTime())->format('Y-m-d'),
        'max' => (new \DateTime('+1 month'))->format('Y-m-d'),
    ],
])
->add('heure', ChoiceType::class, [
    'label' => 'Heure',
    'choices' => $this->generateTimeSlots(8, 20, 30),
    'attr' => ['class' => 'form-select'],
])
->add('cause', TextType::class, [  // Ajout du champ cause
    'label' => 'Cause de la modification',
    'attr' => ['class' => 'form-control'],
    'required' => true, // Rendre ce champ obligatoire
])
->getForm();

    
        // Gérer la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cause = $form->get('cause')->getData();            // Enregistrez la cause dans votre entité ou effectuez d'autres actions nécessaires
            $rendezVous->setCause($cause); // Assurez-vous d'avoir une méthode setCause dans votre entité
        
            $entityManager->flush();
        
            $this->addFlash('success', 'La date du rendez-vous a été modifiée avec succès.');
            return $this->redirectToRoute('medecin_liste_rdv');
        }
         // Envoyer une notification au patient
        
         $cause =  $rendezVous->getCause();
    $medecin = $rendezVous->getMedecin();
    $patient = $rendezVous->getPatient();
    $this->notificationService->createNotificationForPatient(
        $patient,
        'Votre rendez-vous du ' . $rendezVous->getDate()->format('d/m/Y H:i') . 
        ' a été modifié pour la cause suivante : ' .$cause . 
        '<br>avec Dr : ' . $medecin->getUser()->getFirstName() 
    );

        
        return $this->render('medecinRDV/modifier_date_rendez_vous.html.twig', [
            'form' => $form->createView(),
            'rendezVous' => $rendezVous,
        ]);
    }
    
    /**
     * Génère les créneaux horaires disponibles
     */
    private function generateTimeSlots(int $startHour, int $endHour, int $interval)
    {
        $times = [];
        for ($hour = $startHour; $hour < $endHour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += $interval) {
                $dateTime = new \DateTime(sprintf('%02d:%02d', $hour, $minute));
                $formattedTime = $dateTime->format('H:i');
                $times[$formattedTime] = $dateTime; // Stocker DateTime mais afficher H:i
            }
        }
        return $times;
    }
    
    





    // src/Controller/MedecinController.php
    #[Route('/medecin/rendez-vous/{id}/accepter', name: 'accepter_rendez_vous', methods: ['POST'])]
    public function accepterRendezVous(
        int $id,
        RendezVousRepository $rendezVousRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        // Récupérer le rendez-vous
        $rendezVous = $rendezVousRepository->find($id);

        if (!$rendezVous) {
            throw $this->createNotFoundException('Rendez-vous non trouvé.');
        }

        // Mettre à jour le statut du rendez-vous (true pour "Accepté")
        $rendezVous->setStatut(true);

        // Générer un lien Jitsi Meet unique
        $salleJitsi = 'https://meet.jit.si/' . uniqid('consultation_');
        $rendezVous->setLienJitsi($salleJitsi);

        // Enregistrer les modifications
        $entityManager->flush();

        // Envoyer une notification au patient
        $medecin = $rendezVous->getMedecin();
        $patient = $rendezVous->getPatient();
        $this->notificationService->createNotificationForPatient(
            $patient,
            'Votre rendez-vous du ' . $rendezVous->getDate()->format('d/m/Y H:i') . '<br>avec Dr :'. $medecin->getUser()->getFirstName() .' a été accepté.<br> Lien de la consultation : ' . $salleJitsi
        );

        // Envoyer une notification au médecin
        $this->notificationService->createNotificationForMedecin(
            $medecin,
            'Vous avez accepté un rendez-vous avec ' . $patient->getUser()->getFirstName() . ' le ' . $rendezVous->getDate()->format('d/m/Y H:i') . '. Lien de la consultation : ' . $salleJitsi
        );

        // Ajouter un message flash
        $this->addFlash('success', 'Le rendez-vous a été accepté avec succès.');

        // Rediriger vers la liste des rendez-vous
        return $this->redirectToRoute('medecin_liste_rdv');
    }
    //////////////////////////////
#[Route('/rendez-vous/{id}', name: 'rendez_vous_show', methods: ['GET'])]
public function show(RendezVous $rendezVous): Response
{
    return $this->render('rendez_vous/show.html.twig', [
        'rendezVous' => $rendezVous,
    ]);
}
    //////////////////////////////

    #[Route('/medecin/rendez-vous/{id}/refuser', name: 'refuser_rendez_vous', methods: ['POST'])]
    public function refuserRendezVous(
        int $id,
        RendezVousRepository $rendezVousRepository,
        EntityManagerInterface $entityManager,
  
    ): Response {
        // Récupérer le rendez-vous
        $rendezVous = $rendezVousRepository->find($id);
    
        if (!$rendezVous) {
            throw $this->createNotFoundException('Rendez-vous non trouvé.');
        }
    
    
      // Envoyer une notification au patient
      $medecin = $rendezVous->getMedecin();

      $patient = $rendezVous->getPatient();
      $this->notificationService->createNotificationForPatient(
          $patient,
          'Votre rendez-vous du ' . $rendezVous->getDate()->format('d/m/Y H:i') . '<br>avec Dr :'. $medecin->getUser()->getFirstName(). ' a été refusé.  ' 
      );

        // Supprimer le rendez-vous
        $entityManager->remove($rendezVous);
        $entityManager->flush();
    
        // Rediriger vers la liste des rendez-vous
        return $this->redirectToRoute('medecin_liste_rdv');
    }




// src/Controller/MedecinController.php


#[Route('/medecin/rendez-vous/{id}/annuler', name: 'annuler_rendez_vous', methods: ['POST'])]
public function annulerRendezVous(int $id, RendezVousRepository $rendezVousRepository, EntityManagerInterface $entityManager): Response
{
    // Récupérer le rendez-vous
    $rendezVous = $rendezVousRepository->find($id);

    if (!$rendezVous) {
        throw $this->createNotFoundException('Rendez-vous non trouvé.');
    }

    // Annuler le rendez-vous (sans le supprimer)
    $rendezVous->setAnnule(true);
    $entityManager->flush();

   // Envoyer une notification au patient
   $medecin = $rendezVous->getMedecin();
   $patient = $rendezVous->getPatient();
   $this->notificationService->createNotificationForPatient(
       $patient,
       'Votre rendez-vous du ' . $rendezVous->getDate()->format('d/m/Y H:i') . '<br>avec Dr :'. $medecin->getUser()->getFirstName(). ' a été annulé. '
   );


    // Rediriger vers la liste des rendez-vous
    return $this->redirectToRoute('medecin_liste_rdv');
}







/////////////////////////
#[Route('/medecin/modifier-types-rendez-vous', name: 'modifier_types_rendez_vous', methods: ['GET', 'POST'])]
public function modifierTypesRendezVous(
    Request $request,
    EntityManagerInterface $entityManager,
    Security $security
): Response {
    $user = $security->getUser();
    
    
    if (!$user || !$user->getMedecin()) {
        throw $this->createAccessDeniedException('Vous devez être un médecin pour accéder à cette page.');
    }

    $medecin = $user->getMedecin(); 
    $allTypes = ['enligne', 'presentiel', 'hybride']; 
    $activeTypes = $medecin->getTypesRendezVous(); 
    $inactiveTypes = array_diff($allTypes, $activeTypes); 

    if ($request->isMethod('POST')) {
        $type = $request->request->get('type');
        $action = $request->request->get('action');

        if ($type && in_array($type, $allTypes)) { 
            if ($action === 'activer' && !in_array($type, $activeTypes)) {
                $medecin->addTypeRendezVous($type);
            } elseif ($action === 'desactiver' && in_array($type, $activeTypes)) {
                $medecin->removeTypeRendezVous($type);
            }

            $entityManager->flush();
            $this->addFlash('success', "Le type de rendez-vous a été mis à jour.");
        } else {
            $this->addFlash('error', "Type de rendez-vous invalide.");
        }

        return $this->redirectToRoute('modifier_types_rendez_vous');
    }

    return $this->render('medecinRDV/modifier_types_rendez_vous.html.twig', [
        'activeTypes' => $activeTypes,
        'inactiveTypes' => $inactiveTypes,
    ]);
}

//////////////////////////////////////////////////////////////////////////////// meeting

#[Route('/medecin/rendez-vous-acceptes', name: 'medecin_rendez_vous_acceptes')]
public function rendezVousAcceptes(
    Security $security,
    RendezVousRepository $rendezVousRepository
): Response {
    $user = $security->getUser();

    $medecin = $user->getMedecin();

    $rendezVous = $rendezVousRepository->findAcceptedRendezVousByMedecin($medecin);

    return $this->render('medecinRDV/rendez_vous_acceptes.html.twig', [
        'rendezVous' => $rendezVous,
        'medecin' => $medecin,
    ]);
}


#[Route('/medecin/rendez-vous/{id}/show', name: 'rendez_vous_show_medecin', methods: ['GET'])]
public function showRendezVousMedecin(RendezVous $rendezVous): Response
{
    $user = $this->getUser();
    $medecin = $user->getMedecin();

    if (!$user || !$medecin || $medecin->getId() !== $rendezVous->getMedecin()->getId()) {
        throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à accéder à cette consultation.');
    }

    return $this->render('medecinRDV/rendez_vous_show_medecin.html.twig', [
        'rendezVous' => $rendezVous,
       'medecin'=> $medecin,
    ]);
}





#[Route('/notifications', name: 'notifications')]
public function listNotifications(NotificationService $notificationService): Response
{
    $user = $this->getUser();
    $notifications = [];
    $unreadNotificationsCount = 0;

    if ($user->getPatient()) {
        $notifications = $notificationService->getUnreadNotificationsForPatient($user->getPatient());
        $unreadNotificationsCount = count($notifications);
    } elseif ($user->getMedecin()) {
        $notifications = $notificationService->getUnreadNotificationsForMedecin($user->getMedecin());
        $unreadNotificationsCount = count($notifications);
    }

    return $this->render('notification/list.html.twig', [
        'notifications' => $notifications,
        'unreadNotificationsCount' => $unreadNotificationsCount, // Passer le nombre de notifications non lues
    ]);
}
#[Route('/notifications/mark-as-read/{id}', name: 'mark_notification_as_read', methods: ['POST'])]
public function markAsRead(int $id, NotificationService $notificationService): Response
{
    $notification = $notificationService->getNotificationById($id);
    if ($notification) {
        $notificationService->markAsRead($notification);
    }

    return new Response(null, 204); // Retourne une réponse vide avec un code 204 (No Content)
}


}