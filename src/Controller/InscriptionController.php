<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Inscription;
use App\Mailer\TicketMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Knp\Snappy\Pdf;
use Twig\Environment;

class InscriptionController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Pdf $pdfGenerator;
    private Environment $twig;
    private TicketMailer $ticketMailer;

    public function __construct(EntityManagerInterface $entityManager, Pdf $pdfGenerator, Environment $twig, TicketMailer $ticketMailer)
    {
        $this->entityManager = $entityManager;
        $this->pdfGenerator = $pdfGenerator;
        $this->twig = $twig;
        $this->ticketMailer = $ticketMailer;
    }

    #[Route('/inscription/event/{id}', name: 'inscription_event', methods: ['POST'])]
    public function inscrire(Event $event): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Vous devez être connecté pour vous inscrire.'], 401);
        }

        // Vérifier si l'utilisateur est déjà inscrit
        $existingInscription = $this->entityManager->getRepository(Inscription::class)->findOneBy([
            'user' => $user,
            'event' => $event
        ]);

        if ($existingInscription) {
            if ($existingInscription->hasUnsubscribed()) {
                return new JsonResponse(['success' => false, 'message' => 'Vous ne pouvez pas vous réinscrire.'], 400);
            }
            return new JsonResponse(['success' => false, 'message' => 'Vous êtes déjà inscrit.'], 400);
        }

        // Vérifier le nombre d'inscriptions

        if ($event->getPlacesDisponibles() <= 0) {
            return new JsonResponse(['success' => false, 'message' => 'Aucune place disponible.'], 400);
        }

        // Création de l'inscription
        $inscription = new Inscription();
        $inscription->setUser($user);
        $inscription->setEvent($event);
        $inscription->setHasUnsubscribed(false);

        $this->entityManager->persist($inscription);
        $event->decrementPlaces();
        $this->entityManager->flush();

        // ✅ Génération du QR Code
        $qrCodeResult = Builder::create()
            ->data("Inscription ID: " . $inscription->getId())
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(150)
            ->margin(10)
            ->writer(new PngWriter())
            ->build();


        $qrCodeBase64 = base64_encode($qrCodeResult->getString());

        // ✅ Définition du chemin du fichier PDF
        $pdfDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/tickets/';
        if (!is_dir($pdfDirectory)) {
            mkdir($pdfDirectory, 0777, true);
        }

        $pdfFilePath = $pdfDirectory . 'ticket_' . $inscription->getId() . '.pdf';

        // ✅ Génération du PDF
        $html = $this->twig->render('pdf/ticket.html.twig', [
            'event' => $event,
            'user' => $user,
            'inscription' => $inscription,
            'qrCodeBase64' => $qrCodeBase64
        ]);

        $pdfContent = $this->pdfGenerator->getOutputFromHtml($html);
        file_put_contents($pdfFilePath, $pdfContent);

        // ✅ Vérifier si le fichier PDF a bien été généré
        if (!file_exists($pdfFilePath)) {
            return new JsonResponse(['success' => false, 'message' => "Le fichier PDF n'a pas été généré."], 500);
        }

        // ✅ Envoi de l'e-mail avec le billet en pièce jointe
        $this->ticketMailer->sendTicketEmail($user->getEmail(), $pdfFilePath, $event->getTitle(), [
            'user' => $user,
            'event' => $event
        ]);

        // ✅ Supprimer le fichier après un délai
        //sleep(5);
        //unlink($pdfFilePath);

        return new JsonResponse([
            'success' => true,
            'message' => 'Inscription réussie ! Un e-mail avec votre billet a été envoyé.',
        ]);
    }


    #[Route('/inscription/event/{id}/desinscription', name: 'desinscription_event', methods: ['POST'])]
    public function desinscrire(Event $event, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Vous devez être connecté pour vous désinscrire.'], Response::HTTP_UNAUTHORIZED);
        }

        $inscription = $entityManager->getRepository(Inscription::class)->findOneBy([
            'user' => $user,
            'event' => $event
        ]);

        if (!$inscription) {
            return new JsonResponse(['success' => false, 'message' => 'Vous n\'êtes pas inscrit à cet événement.'], Response::HTTP_BAD_REQUEST);
        }

        // Marquer l'inscription comme "désinscrit"
        $inscription->setHasUnsubscribed(true);
        $entityManager->flush();

        // Incrémenter les places disponibles de l'événement
        $event->incrementPlaces();
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Désinscription réussie ! Vous ne pourrez plus vous réinscrire à cet événement.']);
    }


}
