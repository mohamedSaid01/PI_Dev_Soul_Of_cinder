<?php

// src/Controller/TestController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test/session/patient', name: 'test_session_patient')]
    public function simulatePatientSession(Request $request): Response
    {
        // Initialiser une session pour un patient
        $session = $request->getSession();
        $session->set('id_patient', 1); // ID du patient dans la base de données
        $session->set('role', 'pat');   // Rôle "pat" pour patient

        return $this->redirectToRoute('liste_medecins');
    }

    #[Route('/test/session/medecin', name: 'test_session_medecin')]
    public function simulateMedecinSession(Request $request): Response
    {
        // Initialiser une session pour un médecin
        $session = $request->getSession();
        $session->set('id_medecin', 1); // ID du médecin dans la base de données
        $session->set('role', 'med');   // Rôle "med" pour médecin

        return $this->redirectToRoute('medecin_liste_rdv');
    }
}