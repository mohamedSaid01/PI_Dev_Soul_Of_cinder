<?php

namespace App\Controller;

use App\Entity\CategorieEvent;
use App\Form\CategorieEventType;
use App\Repository\CategorieEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


#[Route('/categorie-event')]
class CategorieEventController extends AbstractController
{
    #[Route('/', name: 'categorie_event_index', methods: ['GET'])]
    public function index(CategorieEventRepository $categorieEventRepository): Response
    {
        return $this->render('back/categorie_event/index.html.twig', [
            'categories' => $categorieEventRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'categorie_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorieEvent = new CategorieEvent();
        $form = $this->createForm(CategorieEventType::class, $categorieEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorieEvent);
            $entityManager->flush();

            return $this->redirectToRoute('categorie_event_index');
        }

        return $this->render('back/categorie_event/new.html.twig', [
            'categorieEvent' => $categorieEvent,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'categorie_event_show', methods: ['GET'])]
    public function show(CategorieEvent $categorieEvent): Response
    {
        return $this->render('back/categorie_event/show.html.twig', [
            'categorieEvent' => $categorieEvent,
        ]);
    }

    #[Route('/{id}/edit', name: 'categorie_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CategorieEvent $categorieEvent, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieEventType::class, $categorieEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('categorie_event_index');
        }

        return $this->render('back/categorie_event/edit.html.twig', [
            'categorieEvent' => $categorieEvent,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'categorie_event_delete', methods: ['POST'])]
    public function delete(Request $request, CategorieEvent $categorie, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérifier le token CSRF
        $data = json_decode($request->getContent(), true);
        if (!$this->isCsrfTokenValid('delete', $data['_token'] ?? '')) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide.'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si la catégorie contient des événements
        if (!$categorie->getEvents()->isEmpty()) {
            return new JsonResponse(['success' => false, 'message' => 'Impossible de supprimer une catégorie contenant des événements.']);
        }

        // Supprimer la catégorie
        $entityManager->remove($categorie);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    
    }

    #[Route('/archive/{id}', name: 'categorie_event_archive', methods: ['POST'])]
    public function archive(CategorieEvent $categorie, EntityManagerInterface $entityManager): JsonResponse
    {
        $categorie->setIsArchived(true); // Marque comme archivé
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Catégorie archivée avec succès.']);
    }

    #[Route('/unarchive/{id}', name: 'categorie_event_unarchive', methods: ['POST'])]
    public function unarchive(CategorieEvent $categorie, EntityManagerInterface $entityManager): JsonResponse
    {
        $categorie->setIsArchived(false); // Marque comme non archivé
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Catégorie désarchivée avec succès.']);
    }
}
