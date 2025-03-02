<?php

namespace App\Repository;

use App\Entity\RendezVous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Patient;
<<<<<<< Updated upstream
=======
use App\Entity\Medecin;
>>>>>>> Stashed changes
/**
 * @extends ServiceEntityRepository<RendezVous>
 */
class RendezVousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RendezVous::class);
    }

    //    /**
    //     * @return RendezVous[] Returns an array of RendezVous objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }
    public function findConfirmedRendezVousByPatient(Patient $patient): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.patient = :patient')
<<<<<<< Updated upstream
            ->andWhere('r.statut = :statut')
            ->setParameter('patient', $patient)
            ->setParameter('statut', true) // statut = 1 (confirmé)
=======
            ->setParameter('patient', $patient)
>>>>>>> Stashed changes
            ->orderBy('r.date', 'ASC') // Optionnel : trier par date
            ->addOrderBy('r.heure', 'ASC') // Optionnel : trier par heure
            ->getQuery()
            ->getResult();
    }
    //    public function findOneBySomeField($value): ?RendezVous
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
<<<<<<< Updated upstream
=======

    // src/Repository/RendezVousRepository.php
public function findAccepted(Patient $patient): array
{
    return $this->createQueryBuilder('r')
        ->andWhere('r.patient = :patient')
        ->andWhere('r.statut = :statut')
        ->setParameter('patient', $patient)
        ->setParameter('statut', true) // true = rendez-vous accepté
        ->getQuery()
        ->getResult();
}


public function findAcceptedRendezVousByMedecin(Medecin $medecin): array
{
    return $this->createQueryBuilder('r')
        ->andWhere('r.medecin = :medecin')
        ->andWhere('r.statut = :statut')
        ->setParameter('medecin', $medecin)
        ->setParameter('statut', true) // true = rendez-vous accepté
        ->getQuery()
        ->getResult();
}
>>>>>>> Stashed changes
}
