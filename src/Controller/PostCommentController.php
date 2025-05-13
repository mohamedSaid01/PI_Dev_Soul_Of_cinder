<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Comment;
use App\Form\CommentPostType;
use App\Entity\Post;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

final class PostCommentController extends AbstractController
{
    #[Route('/post/{id}', name: 'app_front_post_show', methods: ['GET', 'POST'])]
    public function show(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get the current page and category from the request
        $page = $request->query->get('page', 1); // Default to page 1 if not provided
        $categoryId = $request->query->get('category', null); // Default to null
        $type = $request->query->get('type', null); // Default to null
        $sortBy = $request->query->get('sortBy', 'newest'); // Default to 'newest'
    
        // Only allow authenticated users to comment
        if ($this->getUser()) {
            // Create a new comment
            $comment = new Comment();
            $comment->setAuthor($this->getUser()); // Assuming you have user authentication
            $comment->setPost($post);
    
            // Create the comment form
            $form = $this->createForm(CommentPostType::class, $comment);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($comment);
                $entityManager->flush();
    
                if ($request->isXmlHttpRequest()) {
                    // Return a JSON response for AJAX requests
                    return $this->json([
                        'success' => true,
                        'comment' => [
                            'id' => $comment->getId(),
                            'content' => $comment->getContent(),
                            'author' => $comment->getAuthor()->getFirstName(),
                            'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i'),
                        ],
                    ]);
                } else {
                    // Redirect to avoid form resubmission
                    return $this->redirectToRoute('app_front_post_show', [
                        'id' => $post->getId(),
                        'page' => $page,
                        'category' => $categoryId,
                        'commentId' => $comment->getId(), // Pass the new comment's ID
                        'type' => $type,
                        'sortBy' => $sortBy,
                    ]);
                }
            }
        } else {
            $form = null;
        }
    
        // Get the comment ID from the request (if any)
        $commentId = $request->query->get('commentId');
    
        return $this->render('front/front_post/show.html.twig', [
            'post' => $post,
            'commentForm' => $form ? $form->createView() : null,
            'currentPage' => $page,
            'currentCategory' => $categoryId,
            'currentType' => $type,
            'currentSortBy' => $sortBy,
            'postId' => $post->getId(), // Pass the post ID
            'commentId' => $commentId, // Pass the comment ID to the template
        ]);
    }

    #[Route('/comment/{id}/delete', name: 'app_comment_delete', methods: ['POST'])]
    public function deleteComment(
        Comment $comment,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Check if the request is an AJAX request
        $isAjax = $request->isXmlHttpRequest();
    
        // Ensure the current user is the author of the comment
        if ($comment->getAuthor() !== $this->getUser()) {
            if ($isAjax) {
                return $this->json(['success' => false, 'message' => 'You are not allowed to delete this comment.'], 403);
            } else {
                throw $this->createAccessDeniedException('You are not allowed to delete this comment.');
            }
        }
    
        // Validate the CSRF token
        if (!$this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            if ($isAjax) {
                return $this->json(['success' => false, 'message' => 'Invalid CSRF token.'], 403);
            } else {
                $this->addFlash('error', 'Invalid CSRF token.');
                return $this->redirectToRoute('app_front_post_show', [
                    'id' => $comment->getPost()->getId(),
                ]);
            }
        }
    
        // Delete the comment
        $entityManager->remove($comment);
        $entityManager->flush();
    
        if ($isAjax) {
            return $this->json(['success' => true, 'commentId' => $comment->getId()]);
        } else {
            return $this->redirectToRoute('app_front_post_show', [
                'id' => $comment->getPost()->getId(),
            ]);
        }
    }
}
