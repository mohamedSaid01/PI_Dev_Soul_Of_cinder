<?php

// src/Controller/ProfileController.php
namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Form\ChangePasswordType;
use App\Form\MedicalFileType;
use App\Service\FileUploader;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ProfileController extends AbstractController
{
    private $security;
    private $fileUploader;

    public function __construct(Security $security, FileUploader $fileUploader)
    {
        $this->security = $security;
        $this->fileUploader = $fileUploader;
    }

    #[Route('/profile/medecin', name: 'profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
    
        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }
    
        // Créer le formulaire de profil
        $profileForm = $this->createForm(ProfileType::class, $user, [
            'user' => $user, // Passer l'utilisateur connecté au formulaire
        ]);
    
        // Créer le formulaire de changement de mot de passe
        $changePasswordForm = $this->createForm(ChangePasswordType::class);
    
        // Créer le formulaire de dossier médical
        $medicalFileForm = $this->createForm(MedicalFileType::class);
    
        // Gérer la soumission du formulaire de profil
        $profileForm->handleRequest($request);
        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            // Enregistrer les modifications en base de données
            $entityManager->flush();
    
            // Ajouter un message de succès
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
    
            // Rediriger vers la page de profil
            return $this->redirectToRoute('profile');
        }
    
        // Gérer la soumission du formulaire de changement de mot de passe
        $changePasswordForm->handleRequest($request);
        if ($changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            $data = $changePasswordForm->getData();
    
            // Récupérer les données du formulaire de changement de mot de passe
            $currentPassword = $data->getCurrentPassword();
            $newPassword = $data->getNewPassword();
            $confirmPassword = $data->getConfirmPassword();
    
            // Valider les données
            if (empty($currentPassword)) {
                $this->addFlash('error', 'Veuillez entrer votre mot de passe actuel.');
                return $this->redirectToRoute('profile');
            }
    
            if (empty($newPassword)) {
                $this->addFlash('error', 'Veuillez entrer un nouveau mot de passe.');
                return $this->redirectToRoute('profile');
            }
    
            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les nouveaux mots de passe ne correspondent pas.');
                return $this->redirectToRoute('profile');
            }
    
            // Vérifier si le mot de passe actuel est correct
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
                return $this->redirectToRoute('profile');
            }
    
            // Hacher et sauvegarder le nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
    
            // Enregistrer les modifications en base de données
            $entityManager->flush();
    
            // Ajouter un message de succès
            $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');
    
            // Rediriger vers la page de profil
            return $this->redirectToRoute('profile');
        }
    
        // Gérer la soumission du formulaire de dossier médical
        $medicalFileForm->handleRequest($request);
        if ($medicalFileForm->isSubmitted() && $medicalFileForm->isValid()) {
            $medicalFile = $medicalFileForm->get('medicalFile')->getData();
    
            if ($medicalFile) {
                // Téléverser le fichier
                $fileName = $this->fileUploader->upload($medicalFile);
    
                // Enregistrer le nom du fichier dans l'entité User
                $user->setMedicalFile($fileName);
    
                // Enregistrer les modifications en base de données
                $entityManager->flush();
    
                // Ajouter un message de succès
                $this->addFlash('success', 'Votre dossier médical a été téléversé avec succès.');
            }
    
            // Rediriger vers la page de profil
            return $this->redirectToRoute('profile');
        }
    
        // Afficher la vue avec les trois formulaires
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'profileForm' => $profileForm->createView(),
            'changePasswordForm' => $changePasswordForm->createView(),
            'medicalFileForm' => $medicalFileForm->createView(),
        ]);
    }


    #[Route('/download-medical-file/{filename}', name: 'download_medical_file')]
    public function downloadMedicalFile(string $filename): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier que l'utilisateur est autorisé à télécharger ce fichier
        if ($user->getMedicalFile() !== $filename) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à télécharger ce fichier.');
        }

        // Chemin du fichier
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $filename;

        // Vérifier que le fichier existe
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Le fichier n\'existe pas.');
        }

        // Retourner le fichier en tant que réponse avec l'en-tête Content-Disposition
        return $this->file($filePath, null, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }


    #[Route('/profile/patient', name: 'profile_patient', methods: ['GET', 'POST'])]
    public function profileMedecin(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
    
        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }
    
        // Créer le formulaire de profil
        $profileForm = $this->createForm(ProfileType::class, $user, [
            'user' => $user, // Passer l'utilisateur connecté au formulaire
        ]);
    
        // Créer le formulaire de changement de mot de passe
        $changePasswordForm = $this->createForm(ChangePasswordType::class);
    
        // Créer le formulaire de dossier médical
        $medicalFileForm = $this->createForm(MedicalFileType::class);
    
        // Gérer la soumission du formulaire de profil
        $profileForm->handleRequest($request);
        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            // Enregistrer les modifications en base de données
            $entityManager->flush();
    
            // Ajouter un message de succès
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
    
            // Rediriger vers la page de profil
            return $this->redirectToRoute('profile_patient');
        }
    
        // Gérer la soumission du formulaire de changement de mot de passe
        $changePasswordForm->handleRequest($request);
        if ($changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            $data = $changePasswordForm->getData();
    
            // Récupérer les données du formulaire de changement de mot de passe
            $currentPassword = $data->getCurrentPassword();
            $newPassword = $data->getNewPassword();
            $confirmPassword = $data->getConfirmPassword();
    
            // Valider les données
            if (empty($currentPassword)) {
                $this->addFlash('error', 'Veuillez entrer votre mot de passe actuel.');
                return $this->redirectToRoute('profile_patient');
            }
    
            if (empty($newPassword)) {
                $this->addFlash('error', 'Veuillez entrer un nouveau mot de passe.');
                return $this->redirectToRoute('profile_patient');
            }
    
            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les nouveaux mots de passe ne correspondent pas.');
                return $this->redirectToRoute('profile_patient');
            }
    
            // Vérifier si le mot de passe actuel est correct
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
                return $this->redirectToRoute('profile_patient');
            }
    
            // Hacher et sauvegarder le nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
    
            // Enregistrer les modifications en base de données
            $entityManager->flush();
    
            // Ajouter un message de succès
            $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');
    
            // Rediriger vers la page de profil
            return $this->redirectToRoute('profile_patient');
        }
    
        // Gérer la soumission du formulaire de dossier médical
        $medicalFileForm->handleRequest($request);
        if ($medicalFileForm->isSubmitted() && $medicalFileForm->isValid()) {
            $medicalFile = $medicalFileForm->get('medicalFile')->getData();
    
            if ($medicalFile) {
                // Téléverser le fichier
                $fileName = $this->fileUploader->upload($medicalFile);
    
                // Enregistrer le nom du fichier dans l'entité User
                $user->setMedicalFile($fileName);
    
                // Enregistrer les modifications en base de données
                $entityManager->flush();
    
                // Ajouter un message de succès
                $this->addFlash('success', 'Votre dossier médical a été téléversé avec succès.');
            }
    
            // Rediriger vers la page de profil
            return $this->redirectToRoute('profile_patient');
        }
    
        // Afficher la vue avec les trois formulaires
        return $this->render('profile/index_patient.html.twig', [
            'user' => $user,
            'profileForm' => $profileForm->createView(),
            'changePasswordForm' => $changePasswordForm->createView(),
            'medicalFileForm' => $medicalFileForm->createView(),
        ]);
    }

}