<?php

namespace App\Service;

use Knp\Snappy\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class PdfGeneratorService
{
    private Pdf $pdf;
    private Environment $twig;

    public function __construct(Pdf $pdf, Environment $twig)
    {
        $this->pdf = $pdf;
        $this->twig = $twig;
    }

    public function generateTicket(int $inscriptionId, string $eventTitle, string $username): Response
    {
        // Génération du QR Code
        $qrCode = Builder::create()
            ->writer(new PngWriter())
            ->data("Inscription ID: $inscriptionId\nÉvénement: $eventTitle\nUtilisateur: $username")
            ->build();

        $qrCodePath = sys_get_temp_dir() . "/qrcode_$inscriptionId.png";
        file_put_contents($qrCodePath, $qrCode->getString());

        // Rendu du template Twig avec le QR Code
        $html = $this->twig->render('pdf/ticket.html.twig', [
            'inscriptionId' => $inscriptionId,
            'eventTitle' => $eventTitle,
            'username' => $username,
            'qrCodePath' => $qrCodePath
        ]);

        // Génération du PDF
        $pdfContent = $this->pdf->getOutputFromHtml($html);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="billet.pdf"',
        ]);
    }
}
