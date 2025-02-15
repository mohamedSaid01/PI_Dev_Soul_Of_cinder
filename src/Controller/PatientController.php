<?php

// src/Controller/PatientController.php

namespace App\Controller;

use App\Entity\User;
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


class PatientController extends AbstractController
{
    #[Route('/admin/patients', name: 'app_patient_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        // Récupérer uniquement les utilisateurs avec le rôle ROLE_PATIENT
        $patients = $userRepository->findByRole('ROLE_USER');

        return $this->render('patient/index.html.twig', [
            'patients' => $patients,
        ]);
    }

    #[Route('/admin/register/patient', name: 'app_patient_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        PasswordGenerator $passwordGenerator, // Injectez le service PasswordGenerator
        MailerInterface $mailer, // Injectez MailerInterface
        UserRepository $userRepository // Injectez UserRepository
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

            // Générer un mot de passe aléatoire
            $plainPassword = $passwordGenerator->generateRandomPassword();

            // Encoder le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Attribuer le rôle ROLE_PATIENT
            $user->setRoles(['ROLE_USER']);

            // Enregistrer l'utilisateur en base de données
            $entityManager->persist($user);
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
}