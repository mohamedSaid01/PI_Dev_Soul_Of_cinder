<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\MedecinRepository;
use App\Repository\RendezVousRepository;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Patient;
class DossierMedicalController extends AbstractController
{
    #[Route('/dossier-medical/{id}', name: 'dossier_medical_show', methods: ['GET'])]
    public function show(Patient $patient): Response
    {
        // Récupérer le dossier médical du patient via l'entité User
        $dossierMedical = $patient->getUser()->getMedicalFile();
    
        return $this->render('dossier_medical/show.html.twig', [
            'dossierMedical' => $dossierMedical,
            'patient' => $patient,
        ]);
    }
}
