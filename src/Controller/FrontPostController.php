<?php

namespace App\Controller;
use App\Entity\Post;
use App\Entity\PostCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\PostType;
use Carbon\Carbon;

final class FrontPostController extends AbstractController
{
    #[Route('/post', name: 'front_list', methods: ['GET'])]
    public function listPosts(Request $request, ManagerRegistry $doctrine): Response
    {
        $categoryId = $request->query->get('category');
        $postRepository = $doctrine->getRepository(Post::class);
        $categoryRepository = $doctrine->getRepository(PostCategory::class);
    
        if ($categoryId) {
            $category = $categoryRepository->find($categoryId);
            $posts = $postRepository->findBy(['category' => $category, 'enabled' => true]);
        } else {
            $posts = $postRepository->findBy(['enabled' => true]);
        }

        foreach ($posts as $post) {
            $post->formattedDate = Carbon::instance($post->getCreatedAt())->diffForHumans();
        }
    
        return $this->render('front/front_post/list.html.twig', [
            'posts' => $posts,
            'categories' => $categoryRepository->findAll(),
            'selectedCategory' => $categoryId
        ]);
    }

    #[Route(path: '/post/create', name: 'front_create')]
    public function createPost(Request $request, ManagerRegistry $doctrine)
    {
        $post = new Post();
    
        // Set default values
        $post->setEnabled(true); // Enabled by default
        $post->setAuthor('user1'); // Author set to "user1"
    
        // Create form
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            
            if ($imageFile) {
                // Ensure the uploads directory exists
                $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';
    
                if (!is_dir($uploadsDirectory)) {
                    mkdir($uploadsDirectory, 0777, true); // Create directory if it doesn't exist
                }
    
                // Generate unique filename
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
    
                // Move file to the uploads directory
                $imageFile->move($uploadsDirectory, $newFilename);
    
                // Save filename in the entity
                $post->setImage($newFilename);
            }
    
            // Save post in the database
            $em = $doctrine->getManager();
            $em->persist($post);
            $em->flush();
    
            $this->addFlash('success', 'Post successfully posted.');
    
            return $this->redirectToRoute('front_list');
        }
    
        return $this->render('front/front_post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/post/delete/{id}', name: 'front_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, ManagerRegistry $doctrine): Response
    {
        // Validate CSRF token
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $post->getId(), $submittedToken)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    
        // Delete the post
        $em = $doctrine->getManager();
        $em->remove($post);
        $em->flush();
    
        $this->addFlash('success', 'Post successfully deleted.');
        return $this->redirectToRoute('front_list');
    }

    #[Route('/post/edit/{id}', name: 'front_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        $imageFile = $form->get('image')->getData();

        // If an image was uploaded
        if ($imageFile) {
            $newFilename = uniqid().'.'.$imageFile->guessExtension();

            // Move the file to the directory where images are stored
            $imageFile->move(
                $this->getParameter('images_directory'), // Define this parameter in services.yaml
                $newFilename
            );

            // Update the 'image' property to store the file name
            $post->setImage($newFilename);
        }

        $post->setCreatedAtValue(); // Preserve original createdAt
        $entityManager = $doctrine->getManager();
        $entityManager->flush();

        $this->addFlash('success', 'Post successfully updated.');
        return $this->redirectToRoute('front_list');
    }

    return $this->render('front/front_post/edit.html.twig', [
        'post' => $post,
        'form' => $form->createView(),
    ]);
    }
    
}
