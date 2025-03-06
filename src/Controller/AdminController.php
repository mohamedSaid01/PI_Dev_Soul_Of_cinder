<?php

// src/Controller/AdminController.php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SmsService;

class AdminController extends AbstractController
{
    #[Route('/admin/verify/user/{id}', name: 'admin_verify_user')]
    public function verifyUser(
        User $user,
        EntityManagerInterface $entityManager,
        SmsService $smsService
    ): Response {
        // Vérifiez que l'utilisateur est un administrateur
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
        // Changez le statut de l'utilisateur
        $user->setStatus('verifie');
        $entityManager->flush();
    
        // Récupérez le numéro de téléphone
        $phoneNumber = $user->getPhoneNumber();
    
        if ($phoneNumber) {
            // Ajoutez le préfixe +216 si nécessaire
            if (!str_starts_with($phoneNumber, '+')) {
                $phoneNumber = '+216' . ltrim($phoneNumber, '0'); // Retirez le "0" initial si présent
            }
    
            // Envoyer un SMS au médecin
            $message = "Hello Dr. {$user->getFirstName()}, your SAHATECK account has been approved. You can now log in.";
    
            try {
                $smsService->sendSms($phoneNumber, $message);
                $this->addFlash('success', 'Le SMS a été envoyé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du SMS : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('warning', 'Le numéro de téléphone est manquant ou invalide.');
        }
    
        // Redirigez vers une page de confirmation ou une liste d'utilisateurs
        return $this->redirectToRoute('admin_user_list');
    }


    #[Route('/admin/users', name: 'admin_user_list')]
    public function listUsers(EntityManagerInterface $entityManager): Response
    {
        // Vérifiez que l'utilisateur est un administrateur
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
        // Récupérez le repository de l'entité User
        $userRepository = $entityManager->getRepository(User::class);
    
        // Construisez la requête avec QueryBuilder
        $users = $userRepository->createQueryBuilder('u')
            ->andWhere('u.status = :status') // Filtre par statut non_verifie
            ->andWhere('u.roles LIKE :role') // Filtre par rôle ROLE_MEDECIN
            ->setParameter('status', 'non_verifie')
            ->setParameter('role', '%"ROLE_MEDECIN"%') // Motif pour JSON
            ->getQuery()
            ->getResult();
    
        return $this->render('admin/user_list.html.twig', [
            'users' => $users,
        ]);
    }
    

#[Route('/admin/user/{id}', name: 'admin_user_show', methods: ['GET'])]
public function showUser(User $user): Response
{
    return $this->render('admin/show.html.twig', [
        'user' => $user,
    ]);
}


#[Route('/admin/user/{id}/delete', name: 'admin_delete_user', methods: ['POST'])]
public function deleteUser(User $user, EntityManagerInterface $entityManager, Request $request): Response
{
    // Vérifiez que l'utilisateur est un administrateur
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    // Vérifiez le token CSRF
    $submittedToken = $request->request->get('_token');
    if (!$this->isCsrfTokenValid('delete' . $user->getId(), $submittedToken)) {
        throw $this->createAccessDeniedException('Token CSRF invalide.');
    }

    // Supprimez l'utilisateur
    $entityManager->remove($user);
    $entityManager->flush();

    // Ajoutez un message flash pour indiquer que la suppression a réussi
    $this->addFlash('success', 'Le médecin a été supprimé avec succès.');

    // Redirigez vers la liste des utilisateurs
    return $this->redirectToRoute('admin_user_list');
}

}