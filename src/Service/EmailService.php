<?php

// src/Service/EmailService.php

namespace App\Service; // Namespace avec une majuscule

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private string $smtpUsername;

    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        //$this->smtpUsername = $smtpUsername;
    }

    public function sendEmail(string $recipientEmail, string $subject, string $content): void
    {
        // Créer un e-mail
        $email = (new Email())
            ->from('sourournajjar2@gmail.com') // Expéditeur
            ->to($recipientEmail) // Destinataire
            ->subject($subject) // Sujet
            ->text($content); // Contenu

        // Envoyer l'e-mail
        $this->mailer->send($email);
    }
}
