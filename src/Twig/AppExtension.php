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
            new TwigFunction('safe_doctor', [$this, 'safeDoctor']),
            new TwigFunction('safe_type_reclamation', [$this, 'safeTypeReclamation']),
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

/**
 * Safely handles doctor entity access, preventing entity not found exceptions
 * 
 * @param mixed $medecin The medecin entity or null
 * @return string The doctor's name or N/A if unavailable
 */
public function safeDoctor($medecin): string
{
    try {
        if ($medecin === null) {
            return 'N/A';
        }
        
        // Verify the doctor exists and has a user property
        if (method_exists($medecin, 'getUser') && $medecin->getUser() !== null) {
            $user = $medecin->getUser();
            
            if (method_exists($user, 'getFirstName') && method_exists($user, 'getLastName')) {
                return $user->getFirstName() . ' ' . $user->getLastName();
            }
        }
        
        return 'N/A';
    } catch (\Exception $e) {
        // Log error if needed
        return 'N/A';
    }
}

/**
 * Safely access TypeReclamation entity avoiding not found exceptions
 * 
 * @param mixed $typeReclamation The typeReclamation entity or null
 * @return string The type of reclamation or N/A if unavailable
 */
public function safeTypeReclamation($typeReclamation): string
{
    try {
        if ($typeReclamation === null) {
            return 'N/A';
        }
        
        // Verify the entity exists and has the required property
        if (method_exists($typeReclamation, 'getTypeReclamation')) {
            $value = $typeReclamation->getTypeReclamation();
            return $value !== null ? $value : 'N/A';
        }
        
        return 'N/A';
    } catch (\Exception $e) {
        // Log error if needed
        return 'N/A';
    }
}

}