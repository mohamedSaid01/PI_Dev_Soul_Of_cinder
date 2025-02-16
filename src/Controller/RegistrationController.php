<?php
// src/Controller/RegistrationController.php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType; // Assurez-vous que ce formulaire est adapté pour les patients
use App\Form\MedecinRegistrationFormType; 
use App\Security\SecurityAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    // src/Controller/RegistrationController.php

#[Route('/choix-inscription', name: 'choix_inscription')]
public function choixInscription(): Response
{
    return $this->render('registration/choix_inscription.html.twig');
}

    #[Route('/register/patient', name: 'app_register_patient')]
    public function registerPatient(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        // Rediriger l'utilisateur s'il est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('display_dashboard');
        }

        // Créer une nouvelle instance de l'entité User
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le mot de passe en clair
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Encoder le mot de passe
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Attribuer le rôle "ROLE_PATIENT" par défaut
            $user->setRoles(['ROLE_USER']);

            // Enregistrer l'utilisateur en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Connecter l'utilisateur automatiquement après l'inscription
            $security->login($user, SecurityAuthenticator::class, 'main');

            // Rediriger vers la page de connexion ou une autre page
            return $this->redirectToRoute('app_login');
        }

        // Afficher le formulaire d'inscription
        return $this->render('registration/register_patient.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    // src/Controller/RegistrationController.php

    #[Route('/register/medecin', name: 'app_register_medecin')]
    public function registerMedecin(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        // Rediriger l'utilisateur s'il est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('display_dashboard');
        }

        // Créer une nouvelle instance de l'entité User
        $user = new User();
        $form = $this->createForm(MedecinRegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le mot de passe en clair
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Encoder le mot de passe
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Attribuer le rôle "ROLE_MEDECIN" par défaut
            $user->setRoles(['ROLE_MEDECIN']);

            // Enregistrer l'utilisateur en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Connecter l'utilisateur automatiquement après l'inscription
            $security->login($user, SecurityAuthenticator::class, 'main');

            // Rediriger vers la page de connexion ou une autre page
            return $this->redirectToRoute('app_login');
        }

        // Afficher le formulaire d'inscription
        return $this->render('registration/register_medecin.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
