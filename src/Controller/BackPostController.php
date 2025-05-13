<?php
namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostCategory;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Comment;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class BackPostController extends AbstractController
{
    #[Route('/back/post/show', name: 'post_index', methods: ['GET'])]
    public function index(Request $request, ManagerRegistry $doctrine, PostRepository $postRepository): Response
    {
        $categoryId = $request->query->get('category');
        // Convert the category ID to an integer 
        $categoryId = $categoryId !== null ? (int)$categoryId : null;
        $type = $request->query->get('type');
        $sort = $request->query->get('sort', 'newest'); // Default to 'newest' if $sort is null
    
        // Fetch all categories
        $categories = $doctrine->getRepository(PostCategory::class)->findAll();
        
        // Fetch unique types from the database
        $types = $postRepository->findUniqueTypes();    
    
        // Fetch filtered and sorted posts
        $posts = $postRepository->findFilteredAndSortedPosts($categoryId, $type, $sort);
    
        return $this->render('back/back_post/index.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
            'types' => $types,
            'selectedCategory' => $categoryId,
            'selectedType' => $type,
            'selectedSort' => $sort,
        ]);
    }

    #[Route('/back/post/delete/{id}', name: 'post_delete', methods: ['POST'])]
public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): JsonResponse
{
    // Décoder le corps de la requête JSON
    $data = json_decode($request->getContent(), true);

    // Vérifier si la requête est valide
    if (!$data || !isset($data['_token'])) {
        return new JsonResponse(['success' => false, 'message' => 'Invalid request.'], Response::HTTP_BAD_REQUEST);
    }

    // Vérifier le token CSRF
    if (!$this->isCsrfTokenValid('delete' . $post->getId(), $data['_token'])) {
        return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
    }

    try {
        // Supprimer le post
        $entityManager->remove($post);
        $entityManager->flush();

        // Retourner une réponse JSON en cas de succès
        return new JsonResponse(['success' => true, 'message' => 'Post deleted successfully.']);
    } catch (\Exception $e) {
        // Retourner une réponse JSON en cas d'erreur
        return new JsonResponse(['success' => false, 'message' => 'An error occurred while deleting the post.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

    #[Route('/post/{id}/toggle-enable', name: 'post_toggle_enable', methods: ['POST'])]
    public function toggleEnable(Post $post, EntityManagerInterface $entityManager): Response
    {
        $post->setEnabled(!$post->isEnabled());
        $entityManager->flush();

    return $this->redirectToRoute('post_index');
}

    #[Route('/back/post/comments/{id}', name: 'comment_delete', methods: ['DELETE'])]
    public function delete_comment(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
{
    // Validate CSRF token
    $submittedToken = $request->request->get('_token');
    if (!$this->isCsrfTokenValid('delete' . $comment->getId(), $submittedToken)) {
        throw $this->createAccessDeniedException('Invalid CSRF token.');
    }

    // Delete the comment
    $entityManager->remove($comment);
    $entityManager->flush();


    return $this->redirectToRoute('post_index'); // Replace with your route
}
}