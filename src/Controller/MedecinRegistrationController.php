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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class MedecinRegistrationController extends AbstractController
{
    #[Route('/admin/register/medecin', name: 'app_register_medecin')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        PasswordGenerator $passwordGenerator
    ): Response {
        $user = new User();
        $form = $this->createForm(MedecinRegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

            // Afficher un message de succès avec le mot de passe généré
            $this->addFlash('success', 'Compte médecin créé avec succès. Mot de passe généré : ' . $plainPassword);

            // Rediriger l'utilisateur après l'inscription
            return $this->redirectToRoute('app_register_medecin');
        }

        return $this->render('registration/medecin_register.html.twig', [
            'medecinRegistrationForm' => $form->createView(),
        ]);
    }
}