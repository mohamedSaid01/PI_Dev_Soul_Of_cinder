<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Service\NotificationService;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $notificationService;
    private $security;

    public function __construct(NotificationService $notificationService, Security $security)
    {
        $this->notificationService = $notificationService;
        $this->security = $security;
    }


    public function getFunctions(): array
    {
        return [
            new TwigFunction('getUnreadNotifications', [$this, 'getUnreadNotifications']),
        ];
    }
    
    public function getUnreadNotifications(): array
{
    $user = $this->security->getUser();
    $unreadNotificationsCount = 0;
    $notifications = [];

    if ($user) {
        if ($user->getPatient()) {
            $notifications = $this->notificationService->getUnreadNotificationsForPatient($user->getPatient());
        } elseif ($user->getMedecin()) {
            $notifications = $this->notificationService->getUnreadNotificationsForMedecin($user->getMedecin());
        }
        $unreadNotificationsCount = count($notifications);
    }

    return [
        'count' => $unreadNotificationsCount,
        'notifications' => $notifications,
    ];
}

}