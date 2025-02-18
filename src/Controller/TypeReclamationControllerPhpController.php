<?php

namespace App\Controller;

use App\Entity\TypeReclamation;
use App\Form\TypeReclamationType;
use App\Repository\TypeReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/type/reclamation/controller/php')]
final class TypeReclamationControllerPhpController extends AbstractController
{
    #[Route(name: 'app_type_reclamation_controller_php_index', methods: ['GET'])]
    public function index(TypeReclamationRepository $typeReclamationRepository): Response
    {
        return $this->render('type_reclamation_controller_php/index.html.twig', [
            'type_reclamations' => $typeReclamationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_type_reclamation_controller_php_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typeReclamation = new TypeReclamation();
        $form = $this->createForm(TypeReclamationType::class, $typeReclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($typeReclamation);
            $entityManager->flush();
            if ($typeReclamation->getTypeReclamation() === 'rendez_vous') {
                return $this->redirectToRoute('app_reclamation_controller_php_new', [], Response::HTTP_SEE_OTHER);
            } elseif ($typeReclamation->getTypeReclamation() === 'produit') {
                return $this->redirectToRoute('app_reclamation_controller_php_new', [], Response::HTTP_SEE_OTHER);
            } elseif ($typeReclamation->getTypeReclamation() === 'autre') { // Correction ici
                return $this->redirectToRoute('app_reclamation_controller_php_new', [], Response::HTTP_SEE_OTHER);
            }
            
            return $this->redirectToRoute('app_type_reclamation_controller_php_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_reclamation_controller_php/new.html.twig', [
            'type_reclamation' => $typeReclamation,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_type_reclamation_controller_php_show', methods: ['GET'])]
    public function show(TypeReclamation $typeReclamation): Response
    {
        return $this->render('type_reclamation_controller_php/show.html.twig', [
            'type_reclamation' => $typeReclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_reclamation_controller_php_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeReclamation $typeReclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TypeReclamationType::class, $typeReclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_type_reclamation_controller_php_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_reclamation_controller_php/edit.html.twig', [
            'type_reclamation' => $typeReclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_reclamation_controller_php_delete', methods: ['POST'])]
public function delete(Request $request, TypeReclamation $typeReclamation, EntityManagerInterface $entityManager): Response
{
    // Récupérer le token CSRF depuis la requête JSON
    $data = json_decode($request->getContent(), true);
    $csrfToken = $data['_token'] ?? '';

    if ($this->isCsrfTokenValid('delete' . $typeReclamation->getId(), $csrfToken)) {
        $entityManager->remove($typeReclamation);
        $entityManager->flush();

        // Retourner une réponse JSON pour l'AJAX
        return $this->json(['success' => true, 'message' => 'Type de réclamation supprimé avec succès']);
    }

    return $this->json(['success' => false, 'message' => 'Token CSRF invalide'], Response::HTTP_FORBIDDEN);
}


}
