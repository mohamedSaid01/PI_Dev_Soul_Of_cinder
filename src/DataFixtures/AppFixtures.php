<?php



// src/DataFixtures/AppFixtures.php
namespace App\DataFixtures;

use App\Entity\EtatRendezVous;
use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Créer des états de rendez-vous
        $enLigne = new EtatRendezVous();
        $enLigne->setLibelle('enligne');
        $manager->persist($enLigne);

        $presentiel = new EtatRendezVous();
        $presentiel->setLibelle('presentiel');
        $manager->persist($presentiel);

        $hybride = new EtatRendezVous();
        $hybride->setLibelle('hybride');
        $manager->persist($hybride);

        // Créer un utilisateur patient
        $patientUser = new User();
        $patientUser->setEmail('patient@example.com');
        $hashedPassword = password_hash('patient123', PASSWORD_BCRYPT);
        $patientUser->setPassword($hashedPassword);        $patientUser->setNom('Doe');
        $patientUser->setPrenom('John');
        $patientUser->setRole('pat');

        $patient = new Patient();
        $patient->setUser($patientUser);
        $manager->persist($patientUser);
        $manager->persist($patient);

        // Créer un utilisateur médecin
        $medecinUser = new User();
        $medecinUser->setEmail('medecin@example.com');
        $hashedPassword = password_hash('medecin123', PASSWORD_BCRYPT);
        $medecinUser->setPassword($hashedPassword);        $medecinUser->setNom('Smith');
        $medecinUser->setPrenom('Dr.');
        $medecinUser->setRole('med');

        $medecin = new Medecin();
        $medecin->setUser($medecinUser);
        $medecin->setSpecialite('Cardiologue');
        $medecin->setTelephone('0123456789');

        // Ajouter des types de rendez-vous acceptés par le médecin
        $medecin->addTypeRendezVous('enligne');
        $medecin->addTypeRendezVous('hybride');

        $manager->persist($medecinUser);
        $manager->persist($medecin);

        // Enregistrer les données
        $manager->flush();
    }
}