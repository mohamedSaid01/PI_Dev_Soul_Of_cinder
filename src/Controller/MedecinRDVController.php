<?php


namespace App\Controller;


use App\Form\RendezVousEditType;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $user = $security->getUser();
        
        if (!$user || !$user->getMedecin()) {
            throw $this->createAccessDeniedException('Vous devez être un médecin pour voir cette page.');
        }
    
        $medecin = $user->getMedecin(); 
    
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
    
        $medecin = $user->getMedecin(); 
    
        // Récupérer les rendez-vous en attente pour ce médecin
        $rendezVousDemandes = $this->rendezVousRepository->findBy([
            'medecin' => $medecin,
            'statut' => false,
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
        'Votre rendez-vous du ' .$rendezVous->getDate()->format('d/m/Y') . ' à ' . $rendezVous->getHeure()->format('H:i')
        . 
        ' a été modifié pour la cause suivante : ' .$cause . 
        'avec Dr : ' . $medecin->getUser()->getFirstName() 
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

        $rendezVous->setStatut(true);

      
        $salleJitsi = 'https://meet.jit.si/' . uniqid('consultation_');
        $rendezVous->setLienJitsi($salleJitsi);

        // Enregistrer les modifications
        $entityManager->flush();

        // Envoyer une notification au patient
        $medecin = $rendezVous->getMedecin();
        $patient = $rendezVous->getPatient();
        // Récupérer la date et l'heure
$date = $rendezVous->getDate();
$heure = $rendezVous->getHeure();

$dateHeure = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $heure->format('H:i'));

// Utiliser $dateHeure pour l'affichage
$this->notificationService->createNotificationForPatient(
    $patient,
    'Votre rendez-vous du ' . $dateHeure->format('d/m/Y H:i') . 'avec Dr : ' . $medecin->getUser()->getFirstName() . ' a été accepté'
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
       'Votre rendez-vous du ' . $rendezVous->getDate()->format('d/m/Y') . ' à ' . $rendezVous->getHeure()->format('H:i')
       . 'avec Dr :'. $medecin->getUser()->getFirstName(). ' a été annulé. '
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
    $allTypes = [1 =>'Présentiel' , 2 => 'Hybride', 3 =>'En ligne' ]; 
    $activeTypes = $medecin->getTypesRendezVous(); 
    $inactiveTypes = array_diff(array_keys($allTypes), $activeTypes); 

    if ($request->isMethod('POST')) {
        $type = $request->request->get('type'); 
        $action = $request->request->get('action');

        if ($type && array_key_exists($type, $allTypes)) { 
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
        'allTypes' => $allTypes,
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
#[Route('/notifications/mark-as-read/{id}', name: 'mark_notification_readM', methods: ['POST'])]
public function markAsRead(int $id, NotificationService $notificationService): Response
{
    $notification = $notificationService->getNotificationById($id);
    if ($notification) {
        $notificationService->markAsRead($notification);
    }

    return new Response(null, 204); // Retourne une réponse vide avec un code 204 (No Content)
}


////////////////////////////////////// Calendrier 



}