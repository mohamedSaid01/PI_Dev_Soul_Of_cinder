<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\PostCategory;
use App\Form\PostCategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;

final class BackCategoryController extends AbstractController
{

    #[Route('/back/ctgpost/show', name: 'post_category_index', methods: ['GET'])]
    public function index(ManagerRegistry $doctrine): Response
    {
        $categories = $doctrine->getRepository(PostCategory::class)->findAll();

        return $this->render('back/back_category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/back/ctgpost/new', name: 'post_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $category = new PostCategory();
        $form = $this->createForm(PostCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $doctrine->getManager();
                try {
                    $em->persist($category);
                    $em->flush();
                    $this->addFlash('success', 'Category created successfully!');
                    return $this->redirectToRoute('post_category_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error creating category. The name might already exist.');
                }
            } else {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('back/back_category/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/back/ctgpost/edit/{id}', name: 'post_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PostCategory $category, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(PostCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->flush();
            
            return $this->redirectToRoute('post_category_index');
        }

        return $this->render('back/back_category/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/back/delete/{id}', name: 'post_category_delete', methods: ['POST'])]
    public function delete(Request $request, PostCategory $category, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true); // Decode JSON request body

        if (!$data || !isset($data['_token'])) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid request.'], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->isCsrfTokenValid('delete' . $category->getId(), $data['_token'])) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        if (!$category->getPosts()->isEmpty()) {
            return new JsonResponse(['success' => false, 'message' => 'Unable to delete a category containing posts.'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->remove($category);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Category deleted successfully.']);
    }
}
