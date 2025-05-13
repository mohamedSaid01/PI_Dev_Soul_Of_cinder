<?php

namespace App\Mailer;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TicketMailer
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendTicketEmail(string $recipient, string $pdfPath, string $eventTitle): void
    {
        $email = (new Email())
            ->from('mohamedsaidboubaker10@gmail.com') // Remplace par ton adresse
            ->to($recipient)
            ->subject("Votre billet pour l'événement : $eventTitle")
            ->text("Merci pour votre inscription. Vous trouverez votre billet en pièce jointe.")
            ->attachFromPath($pdfPath, 'ticket.pdf');

        $this->mailer->send($email);
    }
}
