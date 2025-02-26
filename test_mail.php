<?php

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

require 'vendor/autoload.php';

// Charger le DSN depuis .env
$transport = Transport::fromDsn($_ENV['MAILER_DSN']);
$mailer = new Mailer($transport);

$email = (new Email())
->from('stoustou419@gmail.com')
->to('rayensabri63@gmail.com')
->subject('Test Symfony Mailer')
->text('Ceci est un test d’envoi d’email avec Symfony Mailer.');

$mailer->send($email);

echo "✅ Email envoyé avec succès !";
