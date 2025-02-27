<?php

// src/Controller/PatientController.php
namespace App\Controller;

use App\Entity\Medecin;
use App\Entity\RendezVous;
use App\Form\RendezVousType;
use App\Repository\MedecinRepository;
use App\Repository\RendezVousRepository;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Repository\EtatRendezVousRepository;

class PatientController extends AbstractController
{
    #[Route('/login-patient', name: 'patient_login')]
    public function loginPatient(Request $request): Response
    {
        $idPatient = $request->request->get('id_patient');
        if ($idPatient) {
            $session = $request->getSession();
            $session->set('id_patient', $idPatient);
            $session->set('role', 'pat');

            return $this->redirectToRoute('liste_medecins');
        }

        return $this->render('patient/login.html.twig');
    }

    #[Route('/medecins', name: 'liste_medecins')]
    public function listeMedecins(Request $request, MedecinRepository $medecinRepository): Response
    {
        $session = $request->getSession();
        if (!$session->has('role') || $session->get('role') !== 'pat') {
            return $this->redirectToRoute('patient_login');
        }

        $medecins = $medecinRepository->findAll();
        $typeRendezVous = $request->query->get('typeRendezVous', '');
        $medecins = $medecinRepository->findAll();
        return $this->render('patient/liste_medecins.html.twig', [
            'medecins' => $medecins,
            'typeRendezVous' => $typeRendezVous, // Passer la variable à Twig

        ]);
    }

    #[Route('/prendre-rendez-vous/{id}', name: 'prendre_rendez_vous')]
    public function prendreRendezVous(
        Request $request,
        int $id,
        MedecinRepository $medecinRepository,
        PatientRepository $patientRepository,
        EntityManagerInterface $entityManager,
        RendezVousRepository $rendezVousRepository,
        EtatRendezVousRepository $etatRendezVousRepository
    ): Response {
        $session = $request->getSession();
        if (!$session->has('role') || $session->get('role') !== 'pat') {
            return $this->redirectToRoute('patient_login');
        }
    
        // Récupérer le médecin
        $medecin = $medecinRepository->find($id);
        if (!$medecin) {
            throw $this->createNotFoundException('Médecin introuvable.');
        }
    
        // Récupérer le patient
        $idPatient = $session->get('id_patient');
        $patient = $patientRepository->find($idPatient);
        if (!$patient) {
            throw $this->createNotFoundException('Patient introuvable.');
        }
    
        // Créer un nouveau rendez-vous
        $rendezVous = new RendezVous();
        $rendezVous->setMedecin($medecin);
        $rendezVous->setPatient($patient);
    
        // Créer le formulaire
        $form = $this->createForm(RendezVousType::class, $rendezVous, [
            'medecin' => $medecin,
            'etat_choices' => $this->filterEtatChoicesByMedecin($medecin, $etatRendezVousRepository),
        ]);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Récupérer la date et l'heure séparément
                $date = $rendezVous->getDate();
                $heure = $rendezVous->getHeure();
    
                // Vérifier la disponibilité du médecin
                $this->verifierDisponibiliteMedecin($medecin, $date, $heure, $rendezVousRepository);
    
                // Enregistrer le rendez-vous
                $entityManager->persist($rendezVous);
                $entityManager->flush();
    
                $this->addFlash('success', 'Votre rendez-vous a été pris avec succès.');
                return $this->redirectToRoute('liste_medecins');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }
    
        return $this->render('patient/prendre_rendez_vous.html.twig', [
            'form' => $form->createView(),
            'medecin' => $medecin,
        ]);
    }
    private function verifierDisponibiliteMedecin(Medecin $medecin, \DateTimeInterface $date, \DateTimeInterface $heure, RendezVousRepository $rendezVousRepository): void
    {
        // Vérifier si le médecin a déjà un rendez-vous à cette date et heure
        $existingRendezVous = $rendezVousRepository->findOneBy([
            'medecin' => $medecin,
            'date' => $date,
            'heure' => $heure,
        ]);
    
        if ($existingRendezVous) {
            throw new \Exception('Ce créneau horaire est déjà occupé par ce médecin.');
        }
    
        // Vérifier que la date et l'heure sont dans le futur
        $dateHeure = new \DateTime($date->format('Y-m-d') . ' ' . $heure->format('H:i:s'));
        if ($dateHeure <= new \DateTime('now')) {
            throw new \Exception('Sélectionner une heure disponible.');
        }
    }


    private function filterEtatChoicesByMedecin(Medecin $medecin, EtatRendezVousRepository $etatRendezVousRepository): array
{
    // Récupérer tous les états possibles
    $allEtats = $etatRendezVousRepository->findAll();

    // Filtrer les états selon les préférences du médecin
    $filteredEtats = array_filter($allEtats, function ($etat) use ($medecin) {
        return in_array($etat->getLibelle(), $medecin->getTypesRendezVous());
    });

    return $filteredEtats;
}



    #[Route('/mes-consultations', name: 'mes_consultations')]
    public function mesConsultations(
        Request $request,
        PatientRepository $patientRepository,
        RendezVousRepository $rendezVousRepository
    ): Response {
        // Récupérer la session
        $session = $request->getSession();

        // Vérifier si l'utilisateur est un patient
        if (!$session->has('role') || $session->get('role') !== 'pat') {
            return $this->redirectToRoute('patient_login');
        }

        // Récupérer l'ID du patient depuis la session
        $idPatient = $session->get('id_patient');

        // Récupérer le patient
        $patient = $patientRepository->find($idPatient);
        if (!$patient) {
            throw $this->createNotFoundException('Patient introuvable.');
        }

        // Récupérer les rendez-vous confirmés pour ce patient
        $rendezVous = $rendezVousRepository->findConfirmedRendezVousByPatient($patient);

        return $this->render('patient/mes_consultations.html.twig', [
            'rendezVous' => $rendezVous,
        ]);
    }
}


