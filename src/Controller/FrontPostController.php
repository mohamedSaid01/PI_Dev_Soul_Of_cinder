<?php

namespace App\Controller;

use App\Entity\Post;
use App\Service\ImaggaImageRecognitionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\PostType;
use App\Repository\PostCategoryRepository;
use Carbon\Carbon;
use App\Repository\PostRepository;

final class FrontPostController extends AbstractController
{

    #[Route('/post', name: 'app_front_list', methods: ['GET'])]
    public function listPosts(
        Request $request,
        PostRepository $postRepository,
        PostCategoryRepository $categoryRepository,
        PaginatorInterface $paginator
    ): Response {

        // Get page number from the request
        $page = $request->query->getInt('page', 1); // Default to page 1 if not provided

        // Get the category ID from the request
        $categoryId = $request->query->get('category');
        // Convert the category ID to an integer 
        $categoryId = $categoryId !== null ? (int)$categoryId : null;
        
        // Get the type from the request
        $type = $request->query->get('type', null); // Default to null

        // Get the sorting option from the request
        $sortBy = $request->query->get('sortBy', 'newest'); // Default to 'newest'

        // Fetch posts using the repository method with category and type filters
        $query = $postRepository->findByCategoryAndTypeAndEnabled($categoryId, $type, $sortBy);
    
        // Paginate results
        $pagination = $paginator->paginate(
            $query,
            $page,
            3 // Items per page
        );
    
        // Format dates
        foreach ($pagination as $post) {
            $post->formattedDate = Carbon::instance($post->getCreatedAt())->diffForHumans();
        }

        // Fetch unique types from the database
        $types = $postRepository->findUniqueTypes();        
    
        // Render the template
        return $this->render('front/front_post/list.html.twig', [
            'pagination' => $pagination, // Pass pagination to Twig
            'categories' => $categoryRepository->findAll(),
            'selectedCategory' => $categoryId, // Pass the selected category for filtering
            'selectedType' => $type, // Pass the selected type for filtering
            'types' => $types, // Pass the list of unique types
            'sortBy' => $sortBy, // Pass the selected sorting option
        ]);
    }
    


    #[Route(path: '/post/create', name: 'front_create')]
    public function createPost(
        Request $request,
        ManagerRegistry $doctrine,
        ImaggaImageRecognitionService $imaggaService // Inject the Imagga service
    ): Response {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
    
        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to create a post.');
            return $this->redirectToRoute('app_login'); // Rediriger vers la page de connexion
        }
    
        // Créer un nouveau post
        $post = new Post();
    
        // Définir les valeurs par défaut
        $post->setEnabled(true); // Activé par défaut
        $post->setAuthor($user);
    
        // Créer le formulaire
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
    
            if ($imageFile) {
                // Assurer que le répertoire de téléchargement existe
                $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';
    
                if (!is_dir($uploadsDirectory)) {
                    mkdir($uploadsDirectory, 0777, true); // Créer le répertoire s'il n'existe pas
                }
    
                // Générer un nom de fichier unique
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
    
                // Déplacer le fichier vers le répertoire de téléchargement
                $imageFile->move($uploadsDirectory, $newFilename);
    
                // Enregistrer le nom du fichier dans l'entité
                $post->setImage($newFilename);
    
                // Check if the image contains medical equipment
                $imagePath = $uploadsDirectory . '/' . $newFilename;
                try {
                    if (!$imaggaService->isMedicalEquipment($imagePath)) {
                        // If no medical equipment is detected, show an error message
                        $this->addFlash('error', 'The image is not related to the theme. Please upload a valid image.');
                        return $this->redirectToRoute('front_create');
                    }
                } catch (\RuntimeException $e) {
                    // Handle errors from the Imagga service
                    $this->addFlash('error', 'Error analyzing the image: ' . $e->getMessage());
                    return $this->redirectToRoute('front_create');
                }
            }
    
            // Enregistrer le post dans la base de données
            $em = $doctrine->getManager();
            $em->persist($post);
            $em->flush();
    
    
            return $this->redirectToRoute('app_front_list');
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
    
        // Check if the request is an AJAX request
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'success',
                'message' => 'Post deleted successfully.',
                'postId' => $post->getId(),
            ]);
        }
    
        // Redirect for non-AJAX requests
        return $this->redirectToRoute('app_front_list');
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

        return $this->redirectToRoute('app_front_list');
    }

    return $this->render('front/front_post/edit.html.twig', [
        'post' => $post,
        'form' => $form->createView(),
    ]);
    }
    
}
