<?php
namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class BackPostController extends AbstractController
{
    #[Route('/back/post/show', name: 'post_index', methods: ['GET'])]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $categoryId = $request->query->get('category');
        $entityManager = $doctrine->getManager();
        
        $categories = $doctrine->getRepository(PostCategory::class)->findAll();
        
        if ($categoryId) {
            $posts = $doctrine->getRepository(Post::class)->findBy(['category' => $categoryId]);
        } else {
            $posts = $doctrine->getRepository(Post::class)->findAll();
        }
    
        return $this->render('back/back_post/index.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
            'selectedCategory' => $categoryId,
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
}