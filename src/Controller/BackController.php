<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\NotificationService;
use App\Repository\notificationRepository;
class BackController extends AbstractController
{

    #[Route('/back', name: 'display_dashboard')]
    public function indexAdmin(): Response
    {
        return $this->render('back/index.html.twig'
        );
    }


    #[Route('/notifications', name: 'notifications')]
    public function listNotifications(NotificationService $notificationService): Response
    {
        $user = $this->getUser();
        $notifications = [];
        $unreadNotificationsCount = 0;
    
        if ($user->getPatient()) {
            $notifications = $notificationService->getUnreadNotificationsForPatient($user->getPatient());
            $unreadNotificationsCount = count($notifications);
        } elseif ($user->getMedecin()) {
            $notifications = $notificationService->getUnreadNotificationsForMedecin($user->getMedecin());
            $unreadNotificationsCount = count($notifications);
        }
    
        return $this->render('notification/list.html.twig', [
            'notifications' => $notifications,
            'unreadNotificationsCount' => $unreadNotificationsCount, // Passer la variable ici
        ]);
    }
    

}

