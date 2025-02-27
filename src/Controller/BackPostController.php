<?php
namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$this->isCsrfTokenValid('delete' . $post->getId(), $data['_token'])) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $entityManager->remove($post);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

#[Route('/post/{id}/toggle-enable', name: 'post_toggle_enable', methods: ['POST'])]
public function toggleEnable(Post $post, EntityManagerInterface $entityManager): Response
{
    $post->setEnabled(!$post->isEnabled());
    $entityManager->flush();

return $this->redirectToRoute('post_index');
}
}