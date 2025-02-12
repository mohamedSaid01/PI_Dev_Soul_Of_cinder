<?php
// src/Controller/MedecinRegistrationController.php

namespace App\Controller;

use App\Entity\User;
use App\Form\MedecinRegistrationFormType;
use App\Service\PasswordGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;


class MedecinRegistrationController extends AbstractController
{
    #[Route('/admin/register/medecin', name: 'app_register_medecin')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        PasswordGenerator $passwordGenerator,
        MailerInterface $mailer,
        UserRepository $userRepository // Injecter le repository User
    ): Response {
        $user = new User();
        $form = $this->createForm(MedecinRegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si l'email existe déjà
            $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);

            if ($existingUser) {
                $this->addFlash('error', 'Un utilisateur avec cet email existe déjà.');
                return $this->redirectToRoute('app_register_medecin');
            }

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

            // Enregistrer l'utilisateur en base de données
            $entityManager->persist($user);
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
}