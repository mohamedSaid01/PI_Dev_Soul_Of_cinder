<?php

// src/Controller/PatientController.php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Patient;
use App\Form\PatientType;
use App\Service\PasswordGenerator; // Importez le service PasswordGenerator
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface; // Importez MailerInterface
use Symfony\Component\Mime\Email; // Importez la classe Email
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository; // Importez UserRepository
use App\Form\MedicalFileType;
use App\Service\FileUploader;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\ZeroBounceEmailValidator;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Reponse;


class PatientController extends AbstractController
{
    private ZeroBounceEmailValidator $emailValidator;

    public function __construct(ZeroBounceEmailValidator $emailValidator)
    {
        $this->emailValidator = $emailValidator;
    }

    #[Route('/admin/register/patient', name: 'app_patient_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        PasswordGenerator $passwordGenerator, // Injectez le service PasswordGenerator
        MailerInterface $mailer, // Injectez MailerInterface
        UserRepository $userRepository, // Injectez UserRepository
        ZeroBounceEmailValidator $emailValidator
    ): Response {
        $user = new User();
        $form = $this->createForm(PatientType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
    public function index(Request $request, UserRepository $userRepository, PaginatorInterface $paginator): Response
    {
        // Récupérer le terme de recherche depuis la requête
        $searchTerm = $request->query->get('search');
    
        // Récupérer uniquement les utilisateurs avec le rôle ROLE_USER et isVerified = 1
        $query = $userRepository->createQueryBuilder('u')
            ->where('u.roles LIKE :role') // Filtrer par rôle ROLE_USER
            ->andWhere('u.isVerified = :isVerified') // Filtrer par isVerified = 1
            ->setParameter('role', '%"ROLE_USER"%')
            ->setParameter('isVerified', 1);
    
        // Filtrer les patients par nom et prénom si un terme de recherche est fourni
        if ($searchTerm) {
            $query->andWhere('u.lastName LIKE :searchTerm OR u.firstName LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }
    
        // Paginer les résultats
        $patients = $paginator->paginate(
            $query->getQuery(), // Requête à paginer
            $request->query->getInt('page', 1), // Numéro de la page (par défaut 1)
            2 // Nombre d'éléments par page
        );
    
        return $this->render('patient/index.html.twig', [
            'patients' => $patients,
            'search' => $searchTerm, // Passer le terme de recherche au template
            'pagination_template' => 'pagination/custom_pagination.html.twig',
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
            }
        }
        
        // Chemin du fichier
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $filename;
        
        // Debug : Afficher le chemin du fichier
        dump($filePath);
        
        // Vérifier que le fichier existe
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Le fichier n\'existe pas.');
        }
        
        // Retourner le fichier en tant que réponse avec l'en-tête Content-Disposition
        return $this->file($filePath, null, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }
    
}