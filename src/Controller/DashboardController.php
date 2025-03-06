<?php

// src/Controller/DashboardController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(UserRepository $userRepository): Response
    {
        // Récupérer le nombre total de patients vérifiés (ROLE_USER et isVerified = 1)
        $totalPatients = $userRepository->countByRole('ROLE_USER');

        // Récupérer le nombre total de médecins vérifiés (ROLE_MEDECIN et isVerified = 1)
        $totalMedecins = $userRepository->countByRole('ROLE_MEDECIN');

        // Récupérer le nombre total d'utilisateurs vérifiés (patients + médecins)
        $totalUsers = $totalPatients + $totalMedecins;

            // Récupérer les âges des patients et des médecins
    $patientsAges = $userRepository->findAgesByRole('ROLE_USER');
    $medecinsAges = $userRepository->findAgesByRole('ROLE_MEDECIN');

    // Regrouper les âges par tranches
    $patientsAgeGroups = $this->groupAges($patientsAges);
    $medecinsAgeGroups = $this->groupAges($medecinsAges);

        // Récupérer les statistiques par genre
        $patientsMale = $userRepository->countByRoleAndGender('ROLE_USER', 'male');
        $patientsFemale = $userRepository->countByRoleAndGender('ROLE_USER', 'female');
        $medecinsMale = $userRepository->countByRoleAndGender('ROLE_MEDECIN', 'male');
        $medecinsFemale = $userRepository->countByRoleAndGender('ROLE_MEDECIN', 'female');

        return $this->render('dashboard/index.html.twig', [
            'totalPatients' => $totalPatients,
            'totalMedecins' => $totalMedecins,
            'totalUsers' => $totalUsers, // Passer le nombre total d'utilisateurs au template
            'patientsAgeGroups' => $patientsAgeGroups,
            'medecinsAgeGroups' => $medecinsAgeGroups,
            'patientsMale' => $patientsMale,
            'patientsFemale' => $patientsFemale,
            'medecinsMale' => $medecinsMale,
            'medecinsFemale' => $medecinsFemale,
        ]);
    }

    
private function groupAges(array $ages): array
{
    $groups = [
        '0-10' => 0,
        '11-20' => 0,
        '21-30' => 0,
        '31-40' => 0,
        '41-50' => 0,
        '51-60' => 0,
        '61+' => 0,
    ];

    foreach ($ages as $age) {
        if ($age['age'] <= 10) {
            $groups['0-10']++;
        } elseif ($age['age'] <= 20) {
            $groups['11-20']++;
        } elseif ($age['age'] <= 30) {
            $groups['21-30']++;
        } elseif ($age['age'] <= 40) {
            $groups['31-40']++;
        } elseif ($age['age'] <= 50) {
            $groups['41-50']++;
        } elseif ($age['age'] <= 60) {
            $groups['51-60']++;
        } else {
            $groups['61+']++;
        }
    }

    return $groups;
}
}