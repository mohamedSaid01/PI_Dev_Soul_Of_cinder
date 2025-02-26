<?php 

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\MedecinRegistrationFormType;

class MedecinController extends AbstractController
{
    #[Route('/admin/medecins', name: 'app_medecins_list')]
    public function listMedecins(UserRepository $userRepository): Response
    {
        // Récupérer tous les utilisateurs avec le rôle ROLE_MEDECIN
        $medecins = $userRepository->findByRole('ROLE_MEDECIN');

        return $this->render('medecin/medecins_list.html.twig', [
            'medecins' => $medecins,
        ]);
    }

    // src/Controller/MedecinController.php

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
    $form = $this->createForm(MedecinRegistrationFormType::class, $medecin);
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

}