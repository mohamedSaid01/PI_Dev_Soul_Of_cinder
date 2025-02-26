<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    #[Route('/api/notifications/unread', name: 'api_notifications_unread', methods: ['GET'])]
    public function getUnreadNotifications(NotificationRepository $notificationRepository): JsonResponse
    {
        $notifications = $notificationRepository->findBy(['isRead' => false], ['createdAt' => 'DESC']);
        return $this->json($notifications);
    }

    #[Route('/api/notifications/mark-read', name: 'mark_notifications_read', methods: ['POST'])]
    public function markNotificationsRead(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $notificationIds = $data['ids'] ?? [];

        if (!empty($notificationIds)) {
            $notifications = $entityManager->getRepository(Notification::class)->findBy(['id' => $notificationIds]);

            foreach ($notifications as $notification) {
                $notification->setIsRead(true);
            }

            $entityManager->flush();
        }

        return new JsonResponse(['success' => true]);
    }
}
