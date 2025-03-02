<?php

// src/Controller/PatientController.php
namespace App\Controller;

<<<<<<< Updated upstream
use App\Entity\Medecin;
use App\Entity\RendezVous;
use App\Form\RendezVousType;
use App\Repository\MedecinRepository;
use App\Repository\RendezVousRepository;
use App\Repository\PatientRepository;
=======
use App\Entity\User;
use App\Entity\Patient;
use App\Form\PatientType;
use App\Service\PasswordGenerator; // Importez le service PasswordGenerator
>>>>>>> Stashed changes
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
<<<<<<< Updated upstream
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
=======
use App\Repository\UserRepository; // Importez UserRepository
use App\Form\MedicalFileType;
use App\Service\FileUploader;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\ZeroBounceEmailValidator;
>>>>>>> Stashed changes

use App\Repository\EtatRendezVousRepository;

class PatientController extends AbstractController
{
<<<<<<< Updated upstream
    #[Route('/login-patient', name: 'patient_login')]
    public function loginPatient(Request $request): Response
    {
        $idPatient = $request->request->get('id_patient');
        if ($idPatient) {
            $session = $request->getSession();
            $session->set('id_patient', $idPatient);
            $session->set('role', 'pat');

            return $this->redirectToRoute('liste_medecins');
        }

        return $this->render('patient/login.html.twig');
    }

    #[Route('/medecins', name: 'liste_medecins')]
    public function listeMedecins(Request $request, MedecinRepository $medecinRepository): Response
    {
        $session = $request->getSession();
        if (!$session->has('role') || $session->get('role') !== 'pat') {
            return $this->redirectToRoute('patient_login');
        }

        $medecins = $medecinRepository->findAll();
        $typeRendezVous = $request->query->get('typeRendezVous', '');
        $medecins = $medecinRepository->findAll();
        return $this->render('patient/liste_medecins.html.twig', [
            'medecins' => $medecins,
            'typeRendezVous' => $typeRendezVous, // Passer la variable à Twig

        ]);
=======
    private ZeroBounceEmailValidator $emailValidator;

    public function __construct(ZeroBounceEmailValidator $emailValidator)
    {
        $this->emailValidator = $emailValidator;
>>>>>>> Stashed changes
    }

    #[Route('/prendre-rendez-vous/{id}', name: 'prendre_rendez_vous')]
    public function prendreRendezVous(
        Request $request,
        int $id,
        MedecinRepository $medecinRepository,
        PatientRepository $patientRepository,
        EntityManagerInterface $entityManager,
<<<<<<< Updated upstream
        RendezVousRepository $rendezVousRepository,
        EtatRendezVousRepository $etatRendezVousRepository
=======
        UserPasswordHasherInterface $passwordHasher,
        PasswordGenerator $passwordGenerator, // Injectez le service PasswordGenerator
        MailerInterface $mailer, // Injectez MailerInterface
        UserRepository $userRepository, // Injectez UserRepository
        ZeroBounceEmailValidator $emailValidator
>>>>>>> Stashed changes
    ): Response {
        $session = $request->getSession();
        if (!$session->has('role') || $session->get('role') !== 'pat') {
            return $this->redirectToRoute('patient_login');
        }
    
        // Récupérer le médecin
        $medecin = $medecinRepository->find($id);
        if (!$medecin) {
            throw $this->createNotFoundException('Médecin introuvable.');
        }
    
        // Récupérer le patient
        $idPatient = $session->get('id_patient');
        $patient = $patientRepository->find($idPatient);
        if (!$patient) {
            throw $this->createNotFoundException('Patient introuvable.');
        }
    
        // Créer un nouveau rendez-vous
        $rendezVous = new RendezVous();
        $rendezVous->setMedecin($medecin);
        $rendezVous->setPatient($patient);
    
        // Créer le formulaire
        $form = $this->createForm(RendezVousType::class, $rendezVous, [
            'medecin' => $medecin,
            'etat_choices' => $this->filterEtatChoicesByMedecin($medecin, $etatRendezVousRepository),
        ]);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
<<<<<<< Updated upstream
            try {
                // Récupérer la date et l'heure séparément
                $date = $rendezVous->getDate();
                $heure = $rendezVous->getHeure();
    
                // Vérifier la disponibilité du médecin
                $this->verifierDisponibiliteMedecin($medecin, $date, $heure, $rendezVousRepository);
    
                // Enregistrer le rendez-vous
                $entityManager->persist($rendezVous);
                $entityManager->flush();
    
                $this->addFlash('success', 'Votre rendez-vous a été pris avec succès.');
                return $this->redirectToRoute('liste_medecins');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
=======
            // Vérifier si l'email existe déjà
            $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);

            if ($existingUser) {
                $this->addFlash('error', 'Un utilisateur avec cet email existe déjà.');
                return $this->redirectToRoute('app_patient_new');
            }

            // // Vérifier si l'email est valide et existe sur le serveur de messagerie
            //  if (!$emailValidator->isValid($user->getEmail())) {
            // $this->addFlash('error', 'L\'email n\'est pas valide ou n\'existe pas.');
            // return $this->redirectToRoute('app_patient_new');
            // }

            // Générer un mot de passe aléatoire
            $plainPassword = $passwordGenerator->generateRandomPassword();

            // Encoder le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Attribuer le rôle ROLE_PATIENT
            $user->setRoles(['ROLE_USER']);

            // Définir isVerified à true (1) pour ce patient
            $user->setIsVerified(true);

            $patient = new Patient();
            $patient->setUser($user); // 🔗 Lier le patient à l'utilisateur

            // Enregistrer l'utilisateur en base de données
            $entityManager->persist($user);
            $entityManager->persist($patient);
            $entityManager->flush();

            // Envoyer un email avec les informations de connexion
            $email = (new Email())
                ->from('mohamedsaidboubaker10@gmail.com') // Adresse expéditeur
                ->to($user->getEmail()) // Adresse destinataire
                ->subject('Vos informations de connexion') // Sujet de l'email
                ->html($this->renderView(
                    'emails/patient_registration.html.twig', // Template Twig pour l'email
                    [
                        'email' => $user->getEmail(),
                        'password' => $plainPassword,
                        'firstName' => $user->getFirstName(),
                        'lastName' => $user->getLastName(),
                    ]
                ));

            $mailer->send($email);

            $this->addFlash('success', 'Compte patient créé avec succès. Un email a été envoyé avec les informations de connexion.');
            return $this->redirectToRoute('app_patient_index');
        }

        return $this->render('patient/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/admin/patients', name: 'app_patient_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        // Récupérer uniquement les utilisateurs avec le rôle ROLE_PATIENT
        $patients = $userRepository->findByRole('ROLE_USER');

        return $this->render('patient/index.html.twig', [
            'patients' => $patients,
        ]);
    }


    #[Route('/admin/patient/{id}', name: 'app_patient_show', methods: ['GET'])]
    public function show(User $patient): Response
    {
        return $this->render('patient/show.html.twig', [
            'patient' => $patient,
        ]);
    }

    #[Route('admin/patient/{id}/edit', name: 'app_patient_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $patient, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PatientType::class, $patient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le patient a été modifié avec succès.');
            return $this->redirectToRoute('app_patient_index');
        }

        return $this->render('patient/edit.html.twig', [
            'form' => $form->createView(),
            'patient' => $patient,
        ]);
    }

    #[Route('/admin/patient/{id}/delete', name: 'app_patient_delete', methods: ['POST'])]
    public function delete(Request $request, User $patient, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$patient->getId(), $request->request->get('_token'))) {
            $entityManager->remove($patient);
            $entityManager->flush();

            $this->addFlash('success', 'Le patient a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_patient_index');
    }

    #[Route('/download-medical-file/{filename}', name: 'download_medical_file')]
    public function downloadMedicalFile(string $filename): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        
        // Debug : Afficher les rôles de l'utilisateur
        dump($user->getRoles());
        
        // Vérifier si l'utilisateur est un administrateur
        if (!$this->isGranted('ROLE_ADMIN')) {
            // Si l'utilisateur n'est pas un administrateur, vérifier si le fichier lui appartient
            if ($user->getMedicalFile() !== $filename) {
                throw new AccessDeniedException('Vous n\'êtes pas autorisé à télécharger ce fichier.');
>>>>>>> Stashed changes
            }
        }
    
        return $this->render('patient/prendre_rendez_vous.html.twig', [
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
        RendezVousRepository $rendezVousRepository
    ): Response {
        // Récupérer la session
        $session = $request->getSession();

        // Vérifier si l'utilisateur est un patient
        if (!$session->has('role') || $session->get('role') !== 'pat') {
            return $this->redirectToRoute('patient_login');
        }

        // Récupérer l'ID du patient depuis la session
        $idPatient = $session->get('id_patient');

        // Récupérer le patient
        $patient = $patientRepository->find($idPatient);
        if (!$patient) {
            throw $this->createNotFoundException('Patient introuvable.');
        }

        // Récupérer les rendez-vous confirmés pour ce patient
        $rendezVous = $rendezVousRepository->findConfirmedRendezVousByPatient($patient);

        return $this->render('patient/mes_consultations.html.twig', [
            'rendezVous' => $rendezVous,
        ]);
    }
}


