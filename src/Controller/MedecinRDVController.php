<?php

// src/Controller/MedecinController.php
namespace App\Controller;


use App\Form\RendezVousEditType;

use Symfony\Component\HttpFoundation\RequestStack;

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


class MedecinRDVController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private MedecinRepository $medecinRepository;
    private RendezVousRepository $rendezVousRepository;
    private $session;
    private $requestStack;
    public function __construct(
        EntityManagerInterface $entityManager,
        MedecinRepository $medecinRepository,
        RendezVousRepository $rendezVousRepository,
 
     
        RequestStack $requestStack

    ) {
        $this->entityManager = $entityManager;
        $this->medecinRepository = $medecinRepository;
        $this->rendezVousRepository = $rendezVousRepository;

        $this->requestStack = $requestStack;
    }

   

    #[Route('/medecin/rdv', name: 'medecin_liste_rdv')]
    public function listeRendezVous(Security $security): Response
    {
     
        $user = $security->getUser();
        
       
        if (!$user || !$user->getMedecin()) {
            throw $this->createAccessDeniedException('Vous devez être un médecin pour voir cette page.');
        }
    
        $medecin = $user->getMedecin(); // ✅ Récupération du médecin connecté
    
        $rendezVous = $this->rendezVousRepository->findBy(['medecin' => $medecin]);
    
        return $this->render('medecinRDV/liste_rendez_vous.html.twig', [
            'rendezVous' => $rendezVous,
        ]);
    }

    #[Route('/medecin/rdv/demandes', name: 'medecin_rendez_vous_demandes')]
    public function rendezVousDemandes(Security $security): Response
    {
      
        $user = $security->getUser();
        
      
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
            ->getForm();
    
        // Gérer la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            $this->addFlash('success', 'La date du rendez-vous a été modifiée avec succès.');
            return $this->redirectToRoute('medecin_liste_rdv');
        }
    
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
public function accepterRendezVous(int $id, RendezVousRepository $rendezVousRepository, EntityManagerInterface $entityManager): Response
{
    // Récupérer le rendez-vous
    $rendezVous = $rendezVousRepository->find($id);

    if (!$rendezVous) {
        throw $this->createNotFoundException('Rendez-vous non trouvé.');
    }

    // Mettre à jour le statut du rendez-vous (1 pour "Accepté")
    $rendezVous->setStatut(true); // true = 1
    $entityManager->flush();

    // Ajouter un message flash
    $this->addFlash('success', 'Le rendez-vous a été accepté avec succès.');

    // Rediriger vers la liste des rendez-vous
    return $this->redirectToRoute('medecin_liste_rdv');
}

#[Route('/medecin/rendez-vous/{id}/refuser', name: 'refuser_rendez_vous', methods: ['POST'])]
public function refuserRendezVous(int $id, RendezVousRepository $rendezVousRepository, EntityManagerInterface $entityManager): Response
{
    // Récupérer le rendez-vous
    $rendezVous = $rendezVousRepository->find($id);

    if (!$rendezVous) {
        throw $this->createNotFoundException('Rendez-vous non trouvé.');
    }

    // Supprimer le rendez-vous
    $entityManager->remove($rendezVous);
    $entityManager->flush();

    // Ajouter un message flash
    $this->addFlash('success', 'Le rendez-vous a été refusé et supprimé avec succès.');

    // Rediriger vers la liste des rendez-vous
    return $this->redirectToRoute('medecin_liste_rdv');
}







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

    // Ajouter un message flash
    $this->addFlash('success', 'Le rendez-vous a été annulé avec succès.');

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

    $medecin = $user->getMedecin(); // ✅ Récupération du médecin connecté

    $allTypes = ['enligne', 'presentiel', 'hybride']; // Liste complète des types
    $activeTypes = $medecin->getTypesRendezVous(); // Types activés
    $inactiveTypes = array_diff($allTypes, $activeTypes); // Types désactivés

    if ($request->isMethod('POST')) {
        $type = $request->request->get('type');
        $action = $request->request->get('action');

        if ($type && in_array($type, $allTypes)) { // Vérifier que le type est valide
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
}