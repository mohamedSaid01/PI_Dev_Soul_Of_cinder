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
            if ($failedAttempts >= 3) {
                $user->setIsBlocked(true);
                $user->setBlockedUntil(new \DateTime('+1 minutes')); // Blocage de 1 minute

                // Récupérer l'adresse IP de l'utilisateur
                $ipAddress = $request->headers->get('X-Forwarded-For') ?? $request->getClientIp();

                error_log("Adresse IP récupérée : " . $ipAddress);

                // Convertir l'adresse IP en localisation
                $location = $this->getLocationFromIp($ipAddress);

                error_log("Localisation récupérée : " . $location);

                // Envoyer la notification avec la localisation
                $this->sendBlockedNotification($user, $location);
            }

            // Sauvegarder les modifications dans la base de données
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    /**
     * Envoie un email à l'utilisateur pour l'informer que son compte est bloqué.
     */
/**
 * Sends a security alert to the user.
 */
private function sendBlockedNotification(User $user, string $location): void
{
    // Create the HTML content of the email
    $htmlContent = sprintf(
        '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Security Alert - SAHATECK</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f4f4;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    padding: 30px;
                    background-color: #ffffff;
                    border-radius: 10px;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                    border: 1px solid #e0e0e0;
                }
                h1 {
                    color: #d9534f; /* Red for alert */
                    font-size: 28px;
                    margin-bottom: 25px;
                    text-align: center;
                }
                p {
                    margin: 0 0 20px;
                    font-size: 16px;
                    color: #555;
                }
                .highlight {
                    color: #d9534f;
                    font-weight: bold;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 14px;
                    color: #777;
                    text-align: center;
                    border-top: 1px solid #e0e0e0;
                    padding-top: 20px;
                }
                .footer a {
                    color: #0056b3;
                    text-decoration: none;
                    font-weight: bold;
                }
                .footer a:hover {
                    text-decoration: underline;
                }
                .button {
                    display: inline-block;
                    margin: 25px 0;
                    padding: 12px 24px;
                    color:rgb(51, 124, 226);
                    text-decoration: none;
                    font-size: 16px;
                    text-align: center;
                }
                .button:hover {
                    color:rgb(115, 150, 221);
                }
                .location {
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                    border: 1px solid #e0e0e0;
                    margin: 20px 0;
                    text-align: center;
                    font-size: 18px;
                    color: #333;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Security Alert</h1>
                <p>Hello <strong>%s</strong>,</p>
                <p>We detected a login attempt to your account from the following location:</p>
                <div class="location">
                    <strong>Location:</strong> %s
                </div>
                <p>If this was not you, please take immediate action to secure your account.</p>
                <a href="mailto:support@sahateck.com" class="button">Contact Support</a>
                <p>Best regards,</p>
                <p><strong>The SAHATECK Team</strong></p>
            </div>
            <div class="footer">
                <p>If you have any questions, contact us at <a href="mailto:support@sahateck.com">support@sahateck.com</a>.</p>
                <p>&copy; %s SAHATECK. All rights reserved.</p>
            </div>
        </body>
        </html>
        ',
        $user->getFirstName(), // User's first name
        $location, // Location of the attempt
        date('Y') // Current year for the copyright
    );

    // Create the email
    $email = (new Email())
        ->from('mohamedsaidboubaker10@gmail.com') // Replace with your email address
        ->to($user->getEmail())
        ->subject('Security Alert - Login Attempt Detected')
        ->html($htmlContent); // Use HTML content

    // Send the email
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