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
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\Patient;

use App\Entity\Medecin;
class RegistrationController extends AbstractController
{
    // src/Controller/RegistrationController.php

#[Route('/choix-inscription', name: 'choix_inscription')]
public function choixInscription(): Response
{
    return $this->render('registration/choix_inscription.html.twig');
}

    #[Route('/register/patient', name: 'app_register_patient')]
    public function registerPatient(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, MailerInterface $mailer,): Response
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


            // Générer un token de vérification
            $verificationToken = bin2hex(random_bytes(32));
            $user->setVerificationToken($verificationToken);

   // Créer une nouvelle instance de Patient et l'associer à l'utilisateur
   $patient = new Patient();
   $patient->setUser($user);

   // Enregistrer l'utilisateur et le patient en base de données
   $entityManager->persist($user);
   $entityManager->persist($patient);
   $entityManager->flush();
          
            // Enregistrer l'utilisateur en base de données
           
            // // Connecter l'utilisateur automatiquement après l'inscription
            // $security->login($user, SecurityAuthenticator::class, 'main');

                         // Envoyer l'email de vérification
                         $email = (new TemplatedEmail())
                         ->from('mohamedsaidboubaker10@gmail.com')
                         ->to($user->getEmail())
                         ->subject('Vérification de votre compte')
                         ->htmlTemplate('emails/verification.html.twig')
                         ->context([
                             'user' => $user,
                             'token' => $verificationToken,
                         ]);
            
                     $mailer->send($email);

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
    public function registerMedecin(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, MailerInterface $mailer,): Response
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

            // Générer un token de vérification
            $verificationToken = bin2hex(random_bytes(32));
            $user->setVerificationToken($verificationToken);
   // Créer une nouvelle instance de Patient et l'associer à l'utilisateur
   $medecin = new Medecin();
   $medecin->setUser($user);

   // Enregistrer l'utilisateur et le patient en base de données
   $entityManager->persist($user);
   $entityManager->persist($medecin);
   $entityManager->flush();
            

            // // Connecter l'utilisateur automatiquement après l'inscription
            // $security->login($user, SecurityAuthenticator::class, 'main');

             // Envoyer l'email de vérification
             $email = (new TemplatedEmail())
             ->from('mohamedsaidboubaker10@gmail.com')
             ->to($user->getEmail())
             ->subject('Vérification de votre compte')
             ->htmlTemplate('emails/verification.html.twig')
             ->context([
                 'user' => $user,
                 'token' => $verificationToken,
             ]);

         $mailer->send($email);

            // Rediriger vers la page de connexion ou une autre page
            return $this->redirectToRoute('app_login');
        }

        // Afficher le formulaire d'inscription
        return $this->render('registration/register_medecin.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

// src/Controller/RegistrationController.php
#[Route('/verify-email/{token}', name: 'app_verify_email')]
public function verifyEmail(string $token, EntityManagerInterface $entityManager): Response
{
    $user = $entityManager->getRepository(User::class)->findOneBy(['verificationToken' => $token]);

    if (!$user) {
        throw $this->createNotFoundException('Token invalide.');
    }

    // Activer le compte
    $user->setIsVerified(true);
    $user->setVerificationToken(null); // Supprimer le token après vérification
    $entityManager->flush();

    $this->addFlash('success', 'Votre compte a été vérifié avec succès.');
    return $this->redirectToRoute('app_login');
}
}
