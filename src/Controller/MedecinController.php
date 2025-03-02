<?php

// src/Controller/MedecinController.php
namespace App\Controller;

<<<<<<< Updated upstream

use App\Form\RendezVousEditType;

use Symfony\Component\HttpFoundation\RequestStack;

use App\Form\MedecinPreferencesType;
=======
use App\Entity\User;
use App\Entity\Medecin;
use App\Form\MedecinType;
use App\Service\PasswordGenerator;
>>>>>>> Stashed changes
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MedecinRepository;
use App\Repository\RendezVousRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
<<<<<<< Updated upstream
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class MedecinController extends AbstractController
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

    #[Route('/login-medecin', name: 'medecin_login')]
    public function loginMedecin(Request $request): Response
    {
        // Simuler une connexion médecin avec un ID
        $idMedecin = $request->request->get('id_medecin');
        if ($idMedecin) {
            // Stocker les informations du médecin dans la session
            $session = $request->getSession();
            $session->set('id_medecin', $idMedecin);
            $session->set('role', 'med');

            return $this->redirectToRoute('medecin_liste_rdv');
        }

        return $this->render('medecin/login.html.twig');
    }

    #[Route('/medecin/rdv', name: 'medecin_liste_rdv')]
    public function listeRendezVous(Request $request): Response
    {
        // Vérifier si une session est ouverte pour un médecin
        $session = $request->getSession();
        if (!$session->has('role') || $session->get('role') !== 'med') {
            return $this->redirectToRoute('medecin_login');
        }

        // Récupérer l'ID du médecin depuis la session
        $idMedecin = $session->get('id_medecin');
        $medecin = $this->medecinRepository->find($idMedecin);

        if (!$medecin) {
            throw $this->createNotFoundException('Médecin introuvable.');
        }

        // Récupérer tous les rendez-vous du médecin
        $rendezVous = $this->rendezVousRepository->findBy(['medecin' => $medecin]);

        return $this->render('medecin/liste_rendez_vous.html.twig', [
            'rendezVous' => $rendezVous,
        ]);
    }

    #[Route('/medecin/rdv/demandes', name: 'medecin_rendez_vous_demandes')]
    public function rendezVousDemandes(Request $request): Response
    {
        // Vérifier si une session est ouverte pour un médecin
        $session = $request->getSession();
        if (!$session->has('role') || $session->get('role') !== 'med') {
            return $this->redirectToRoute('medecin_login');
        }

        // Récupérer l'ID du médecin depuis la session
        $idMedecin = $session->get('id_medecin');
        $medecin = $this->medecinRepository->find($idMedecin);

        if (!$medecin) {
            throw $this->createNotFoundException('Médecin introuvable.');
        }

        // Récupérer les rendez-vous demandés pour ce médecin (statut = false)
        $rendezVousDemandes = $this->rendezVousRepository->findBy([
            'medecin' => $medecin,
            'statut' => false, // Seulement les rendez-vous en attente
        ]);

        return $this->render('medecin/rendez_vous_demandes.html.twig', [
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
    
        return $this->render('medecin/modifier_date_rendez_vous.html.twig', [
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

    // Ajouter un message flash
    $this->addFlash('success', 'Le rendez-vous a été annulé avec succès.');

    // Rediriger vers la liste des rendez-vous
    return $this->redirectToRoute('medecin_liste_rdv');
}







/////////////////////////
#[Route('/medecin/modifier-types-rendez-vous', name: 'modifier_types_rendez_vous', methods: ['GET', 'POST'])]
public function modifierTypesRendezVous(Request $request, EntityManagerInterface $entityManager): Response
{
    $session = $this->requestStack->getSession();

    if (!$session->has('role') || $session->get('role') !== 'med') {
        return $this->redirectToRoute('medecin_login');
    }

    $idMedecin = $session->get('id_medecin');
    $medecin = $this->medecinRepository->find($idMedecin);

    if (!$medecin) {
        throw $this->createNotFoundException('Médecin non trouvé.');
    }

    $allTypes = ['enligne', 'presentiel', 'hybride']; // Liste complète des types
    $activeTypes = $medecin->getTypesRendezVous(); // Types activés
    $inactiveTypes = array_diff($allTypes, $activeTypes); // Types désactivés

    if ($request->isMethod('POST')) {
        $type = $request->request->get('type');
        $action = $request->request->get('action');

        if ($action === 'activer') {
            $medecin->addTypeRendezVous($type);
        } elseif ($action === 'desactiver') {
            $medecin->removeTypeRendezVous($type);
        }

        $entityManager->flush();
        $this->addFlash('success', "Le type de rendez-vous a été mis à jour.");

        return $this->redirectToRoute('modifier_types_rendez_vous');
    }

    return $this->render('medecin/modifier_types_rendez_vous.html.twig', [
        'activeTypes' => $activeTypes,
        'inactiveTypes' => $inactiveTypes,
    ]);
}

=======
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Service\ZeroBounceEmailValidator;

class MedecinController extends AbstractController
{
    private ZeroBounceEmailValidator $emailValidator;

    public function __construct(ZeroBounceEmailValidator $emailValidator)
    {
        $this->emailValidator = $emailValidator;
    }

    #[Route('/admin/new-register/medecin', name: 'app_new_register_medecin')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        PasswordGenerator $passwordGenerator,
        MailerInterface $mailer,
        UserRepository $userRepository,
        ZeroBounceEmailValidator $emailValidator // Injection directe
    ): Response {
        $user = new User();
        $form = $this->createForm(MedecinType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si l'email existe déjà
            $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);

            if ($existingUser) {
                $this->addFlash('error', 'Un utilisateur avec cet email existe déjà.');
                return $this->redirectToRoute('app_new_register_medecin');
            }

            //  // Vérifier si l'email est valide et existe sur le serveur de messagerie
            //  if (!$emailValidator->isValid($user->getEmail())) {
            //     $this->addFlash('error', 'L\'email n\'est pas valide ou n\'existe pas.');
            //     return $this->redirectToRoute('app_new_register_medecin');
            // }           

            // Générer un mot de passe aléatoire
            $plainPassword = $passwordGenerator->generateRandomPassword();

            // Encoder le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $plainPassword
                )
            );

            // Attribuer le rôle ROLE_MEDECIN
            $user->setRoles(['ROLE_MEDECIN']);

            // Définir isVerified à true (1) pour ce patient
            $user->setIsVerified(true);

            $medecin = new Medecin();
            $medecin->setUser($user);

            // Enregistrer l'utilisateur en base de données
            $entityManager->persist($user);
            $entityManager->persist($medecin);
            $entityManager->flush();

            // Envoyer un email avec les informations de connexion
            $email = (new Email())
                ->from('mohamedsaidboubaker10@gmail.com')
                ->to($user->getEmail())
                ->subject('Vos informations de connexion')
                ->html($this->renderView(
                    'emails/medecin_registration.html.twig',
                    [
                        'email' => $user->getEmail(),
                        'password' => $plainPassword,
                        'firstName' => $user->getFirstName(),
                        'lastName' => $user->getLastName(),
                    ]
                ));

            $mailer->send($email);

            $this->addFlash('success', 'Compte médecin créé avec succès. Un email a été envoyé avec les informations de connexion.');
            return $this->redirectToRoute('app_medecins_list');
        }

        return $this->render('registration/medecin_register.html.twig', [
            'medecinRegistrationForm' => $form->createView(),
        ]);
    }
    
    #[Route('/admin/medecins', name: 'app_medecins_list')]
    public function listMedecins(UserRepository $userRepository): Response
    {
        // Récupérer tous les utilisateurs avec le rôle ROLE_MEDECIN
        $medecins = $userRepository->findByRole('ROLE_MEDECIN');

        return $this->render('medecin/medecins_list.html.twig', [
            'medecins' => $medecins,
        ]);
    }

    #[Route('/admin/medecin/{id}', name: 'app_medecin_show', methods: ['GET'])]
    public function showMedecin(User $medecin): Response
    {
        return $this->render('medecin/show.html.twig', [
            'medecin' => $medecin,
        ]);
    }

    #[Route('/admin/medecin/{id}/edit', name: 'app_medecin_edit', methods: ['GET', 'POST'])]
    public function editMedecin(Request $request, User $medecin, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MedecinType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le médecin a été mis à jour avec succès.');
            return $this->redirectToRoute('app_medecins_list');
        }

        return $this->render('medecin/edit.html.twig', [
            'medecin' => $medecin,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/medecin/{id}/delete', name: 'app_medecin_delete', methods: ['POST'])]
    public function deleteMedecin(Request $request, User $medecin, EntityManagerInterface $entityManager): Response
    {
        // Vérifier le token CSRF pour sécuriser la suppression
        if ($this->isCsrfTokenValid('delete' . $medecin->getId(), $request->request->get('_token'))) {
            // Supprimer le médecin de la base de données
            $entityManager->remove($medecin);
            $entityManager->flush();

            // Ajouter un message de succès
            $this->addFlash('success', 'Le médecin a été supprimé avec succès.');
        } else {
            // Ajouter un message d'erreur si le token CSRF est invalide
            $this->addFlash('error', 'Token CSRF invalide, suppression annulée.');
        }

        // Rediriger vers la liste des médecins
        return $this->redirectToRoute('app_medecins_list');
    }

>>>>>>> Stashed changes
}