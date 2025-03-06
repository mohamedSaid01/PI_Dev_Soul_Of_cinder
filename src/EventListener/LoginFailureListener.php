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
use Symfony\Component\HttpFoundation\Request;

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
            // Vérifier si le blocage est terminé
            $now = new \DateTime();
            if ($user->getBlockedUntil() && $user->getBlockedUntil() <= $now) {
                // Réinitialiser les champs après la fin du blocage
                $user->setIsBlocked(false);
                $user->setBlockedUntil(null);
                $user->setFailedLoginAttempts(0); // Réinitialiser failedLoginAttempts à 0 après le blocage

                // Sauvegarder les modifications
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                return; // Sortir de la méthode car l'utilisateur est déjà débloqué
            }

             // Si l'utilisateur est déjà bloqué, ne pas incrémenter failedLoginAttempts
             if ($user->getIsBlocked()) {
                return; // Sortir de la méthode car l'utilisateur est déjà bloqué
            }

            // Incrémenter le nombre de tentatives infructueuses
            $failedAttempts = $user->getFailedLoginAttempts() + 1;
            $user->setFailedLoginAttempts($failedAttempts);

            // Bloquer l'utilisateur après 3 tentatives infructueuses
            if ($failedAttempts == 3) {
                $user->setIsBlocked(true);
                $user->setBlockedUntil(new \DateTime('+1 minutes')); // Blocage de 1 minute
            
                // Récupérer l'adresse IP de l'utilisateur
                $ipAddress = $this->getClientIp($request); // Utilisation de getClientIp()
            
                error_log("Adresse IP récupérée : " . $ipAddress);
            
                // Convertir l'adresse IP en localisation et coordonnées géographiques
                $locationData = $this->getLocationFromIp($ipAddress);
                $location = $locationData['location'];
                $geoData = $locationData['geoData'];
            
                error_log("Localisation récupérée : " . $location);
            
                // Envoyer la notification avec la localisation et les coordonnées géographiques
                $this->sendBlockedNotification($user, $location, $geoData);
            }
            // Sauvegarder les modifications dans la base de données
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }


    private function getClientIp(Request $request): string
    {
        // Récupérer l'IP depuis X-Forwarded-For si disponible (utile derrière un proxy)
        $ipAddress = $request->headers->get('X-Forwarded-For');
    
        // Si X-Forwarded-For n'est pas défini, utiliser getClientIp()
        if (!$ipAddress) {
            $ipAddress = $request->getClientIp();
        }
    
        // Gérer les cas où plusieurs IPs sont séparées par des virgules (format commun pour X-Forwarded-For)
        if ($ipAddress && strpos($ipAddress, ',') !== false) {
            $ips = explode(',', $ipAddress);
            $ipAddress = trim($ips[0]); // Prendre la première IP de la liste
        }
    
        // Si l'IP est localhost, récupérer l'IP publique via un service externe
        if (in_array($ipAddress, ['127.0.0.1', '::1'])) {
            try {
                // Utiliser un service externe pour obtenir l'IP publique
                $externalIp = file_get_contents('https://api64.ipify.org');
                if ($externalIp) {
                    $ipAddress = $externalIp;
                }
            } catch (\Exception $e) {
                error_log("Impossible de récupérer l'IP publique : " . $e->getMessage());
                $ipAddress = 'Inconnue'; // En cas d'échec, marquer l'IP comme inconnue
            }
        }
    
        return $ipAddress;
    }

    /**
     * Envoie un email à l'utilisateur pour l'informer que son compte est bloqué.
     */
/**
 * Sends a security alert to the user.
 */
private function sendBlockedNotification(User $user, string $location, array $geoData = []): void
{
    // Extraire les données géographiques si disponibles
    $latitude = $geoData['loc'] ?? null;
    $googleMapsLink = '';

    if ($latitude) {
        [$lat, $lon] = explode(',', $latitude);
        $googleMapsLink = "https://www.google.com/maps?q=$lat,$lon";
    }

    // Créer le contenu HTML de l'email
    $htmlContent = sprintf(
        '
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        color: #333;
                        margin: 0;
                        padding: 0;
                    }
                    .email-container {
                        max-width: 600px;
                        margin: 20px auto;
                        padding: 20px;
                        background-color: #fff;
                        border-radius: 8px;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }
                    h1 {
                        color: #d9534f;
                        font-size: 24px;
                        margin-bottom: 20px;
                    }
                    p {
                        font-size: 16px;
                        line-height: 1.6;
                        margin-bottom: 20px;
                    }
                    a {
                        color: #337ab7;
                        text-decoration: none;
                    }
                    a:hover {
                        text-decoration: underline;
                    }
                    .button {
                        display: inline-block;
                        padding: 10px 20px;
                        background-color: #d9534f;
                        color: #fff;
                        font-size: 16px;
                        border-radius: 5px;
                        text-decoration: none;
                        margin-top: 20px;
                    }
                    .button:hover {
                        background-color: #c9302c;
                    }
                    .footer {
                        margin-top: 30px;
                        font-size: 14px;
                        color: #777;
                        text-align: center;
                    }
                </style>
            </head>
            <body>
                <div class="email-container">
                    <h1>Security Alert</h1>
                    <p>Hello %s,</p>
                    <p>We detected a login attempt to your account from the following location:</p>
                    <p><strong>Location:</strong> %s</p>
                    <p>%s</p>
                    <p>If this was not you, please take immediate action to secure your account.</p>
                    %s
                    <p>If you have any questions, contact us at <a href="mailto:support@sahateck.com">support@sahateck.com</a>.</p>
                    <div class="footer">
                        <p>Best regards,<br>The SAHATECK Team</p>
                        <p>© %s SAHATECK. All rights reserved.</p>
                    </div>
                </div>
            </body>
        </html>
        ',
        $user->getFirstName(), // Prénom de l'utilisateur
        $location, // Localisation de la tentative
        $googleMapsLink ? 'A map of the location is available below:' : '',
        $googleMapsLink ? '<p><a class="button" href="' . $googleMapsLink . '" target="_blank">View Location on Google Maps</a></p>' : '',
        date('Y') // Année actuelle pour le copyright
    );

    // Créer l'email
    $email = (new Email())
        ->from('mohamedsaidboubaker10@gmail.com') // Remplacez par votre adresse e-mail
        ->to($user->getEmail())
        ->subject('Security Alert - Login Attempt Detected')
        ->html($htmlContent); // Utiliser le contenu HTML

    // Envoyer l'email
    $this->mailer->send($email);
}

    /**
     * Convertit une adresse IP en localisation (ville, pays) en utilisant l'API ipinfo.io.
     */
    private function getLocationFromIp(string $ipAddress): array
    {
        // Ignorer les adresses IP locales
        if (in_array($ipAddress, ['127.0.0.1', '::1'])) {
            return ['location' => 'Local (non géolocalisable)', 'geoData' => []];
        }
    
        try {
            $apiToken = '0ca51938b4cd4d'; // Remplacez par votre token API ipinfo.io
            $url = "https://ipinfo.io/{$ipAddress}?token={$apiToken}";
    
            $response = file_get_contents($url);
            $data = json_decode($response, true);
    
            // Log pour déboguer la réponse
            error_log("Réponse de l'API : " . print_r($data, true));
    
            if (isset($data['error'])) {
                return ['location' => 'Localisation inconnue (erreur API)', 'geoData' => []];
            }
    
            $city = $data['city'] ?? 'Inconnu';
            $country = $data['country'] ?? 'Inconnu';
            $location = "{$city}, {$country}";
            $geoData = isset($data['loc']) ? ['loc' => $data['loc']] : [];
    
            return ['location' => $location, 'geoData' => $geoData];
        } catch (\Exception $e) {
            error_log("Erreur lors de la récupération de la localisation : " . $e->getMessage());
            return ['location' => 'Localisation inconnue (exception)', 'geoData' => []];
        }
    }
}