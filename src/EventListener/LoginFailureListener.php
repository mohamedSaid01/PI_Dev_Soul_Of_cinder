<?php

// src/EventListener/LoginFailureListener.php

namespace App\EventListener;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class LoginFailureListener implements EventSubscriberInterface
{
    private $userRepository;
    private $mailer;
    private $entityManager;

    public function __construct(UserRepository $userRepository, MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $request = $event->getRequest();
        $username = $request->request->get('email'); // Utilisez 'email' au lieu de '_username'
        $user = $this->userRepository->findOneBy(['email' => $username]);

        if ($user instanceof User) {
            $failedAttempts = $user->getFailedLoginAttempts() + 1;
            $user->setFailedLoginAttempts($failedAttempts);

            if ($failedAttempts >= 3) {
                $user->setIsBlocked(true);
                $user->setBlockedUntil(new \DateTime('+1 hour'));

                // Récupérer l'adresse IP de l'utilisateur
                $ipAddress = $request->headers->get('X-Forwarded-For') ?? $request->getClientIp();

                error_log("Adresse IP récupérée : " . $ipAddress);

                // Convertir l'adresse IP en localisation
                $location = $this->getLocationFromIp($ipAddress);

                error_log("Localisation récupérée : " . $location);

                // Envoyer la notification avec la localisation
                $this->sendBlockedNotification($user, $location);
            }

            // Utilisez l'EntityManager pour sauvegarder les modifications
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    private function sendBlockedNotification(User $user, string $location): void
    {
        $email = (new Email())
            ->from('mohamedsaidboubaker10@gmail.com')
            ->to($user->getEmail())
            ->subject('Votre compte a été bloqué')
            ->text(sprintf(
                'Votre compte a été bloqué après 3 tentatives de connexion infructueuses. Localisation : %s',
                $location
            ));

        $this->mailer->send($email);
    }

    /**
     * Convertit une adresse IP en localisation (ville, pays) en utilisant l'API ipinfo.io.
     */
    private function getLocationFromIp(string $ipAddress): string
    {
        // Ignorer les adresses IP locales
        if (in_array($ipAddress, ['127.0.0.1', '::1'])) {
            return 'Local (non géolocalisable)';
        }
    
        // Utiliser un service de géolocalisation pour les adresses IP publiques
        try {
            $apiToken = '0ca51938b4cd4d'; // Remplacez par votre token API ipinfo.io
            $url = "https://ipinfo.io/{$ipAddress}?token={$apiToken}";
    
            $response = file_get_contents($url);
            $data = json_decode($response, true);
    
            // Log pour déboguer la réponse
            error_log("Réponse de l'API : " . print_r($data, true));
    
            if (isset($data['error'])) {
                return 'Localisation inconnue (erreur API)';
            }
    
            $city = $data['city'] ?? 'Inconnu';
            $country = $data['country'] ?? 'Inconnu';
    
            return "{$city}, {$country}";
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération de la localisation : " . $e->getMessage());
            return 'Localisation inconnue (exception)';
        }
    }
}