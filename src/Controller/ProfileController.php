<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/profile', name: 'profile', methods: ['GET'])]
    public function index(): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login'); // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
        }

        // Renvoyer la vue unique avec les données de l'utilisateur
        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }


    #[Route('/profile', name: 'profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Créer le formulaire
        $form = $this->createForm(ProfileType::class, $user, [
            'user' => $user, // Passer l'utilisateur connecté au formulaire
        ]);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer les modifications en base de données
            $entityManager->flush();

            // Ajouter un message de succès
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            // Rediriger vers la page de profil
            return $this->redirectToRoute('profile');
        }

        // Afficher la vue avec le formulaire
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(), // Passer le formulaire à la vue
        ]);
    }
}
