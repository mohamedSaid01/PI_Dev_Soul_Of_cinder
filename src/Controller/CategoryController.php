<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/category')]
final class CategoryController extends AbstractController
{
    #[Route(name: 'app_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if the category name already exists
            $existingCategory = $categoryRepository->findOneBy(['name' => $category->getName()]);
            if ($existingCategory) {
                $this->addFlash('error', 'Cette catégorie existe déjà.');
                return $this->redirectToRoute('app_category_new');
            }

            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if the category name already exists
            $existingCategory = $categoryRepository->findOneBy(['name' => $category->getName()]);
            if ($existingCategory && $existingCategory->getId() !== $category->getId()) {
                $this->addFlash('error', 'Cette catégorie existe déjà.');
                return $this->redirectToRoute('app_category_edit', ['id' => $category->getId()]);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);

    }
    

   

  
    #[Route('/delete/{id}', name: 'categorie_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérifier le token CSRF
        $data = json_decode($request->getContent(), true);
        if (!$this->isCsrfTokenValid('delete' . $category->getId(), $data['_token'] ?? '')) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide.'], Response::HTTP_BAD_REQUEST);
        }
    
        // Vérifier si la catégorie contient des produits
        if (!$category->getProduits()->isEmpty()) {
            return new JsonResponse(['success' => false, 'message' => 'Unable to delete a category containing Products.']);
        }
    
        // Supprimer la catégorie
        $entityManager->remove($category);
        $entityManager->flush();
        
        $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        return new JsonResponse(['success' => true]);
        

    }
}