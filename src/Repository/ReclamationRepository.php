<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reclamation>
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

    // src/Repository/ReclamationRepository.php
// src/Repository/ReclamationRepository.php
// src/Repository/ReclamationRepository.php
// ReclamationRepository.php

public function searchReclamations(?string $description, ?string $date, ?string $medecin): array
{
    $qb = $this->createQueryBuilder('r');

    // Recherche par description (si fournie)
    if ($description) {
        $qb->andWhere('r.description LIKE :description')
           ->setParameter('description', '%' . $description . '%');
    }

    // Recherche par date (si fournie)
    if ($date) {
        $qb->andWhere('r.date = :date')
           ->setParameter('date', $date);
    }

    // Recherche par nom de médecin (si fournie)
    if ($medecin) {
        $qb->join('r.medecin', 'm') // Supposons que 'medecin' est une relation dans l'entité Reclamation
           ->andWhere('m.nom LIKE :medecin')
           ->setParameter('medecin', '%' . $medecin . '%');
    }

    return $qb->getQuery()->getResult();
}

public function findByType(string $typeReclamation): array
{
    return $this->createQueryBuilder('r')
        ->andWhere('r.typereclamation = :typereclamation')
        ->setParameter('typereclamation', $typeReclamation)
        ->getQuery()
        ->getResult();
}


public function findByFilters(?string $description, ?string $medecin_id, ?int $typeReclamationId): array
{
    $qb = $this->createQueryBuilder('r');

    if ($description) {
        $qb->andWhere('r.description LIKE :description')
           ->setParameter('description', '%' . $description . '%');
    }

    if ($medecin_id) {
        $qb->join('r.Medecin', 'm')
           ->andWhere('m.nom LIKE :Medecin')
           ->setParameter('Medecin', '%' . $medecin_id . '%');
    }

    if ($typeReclamationId) {
        $qb->join('r.typeReclamation', 't')
           ->andWhere('t.id = :typeReclamationId')
           ->setParameter('typeReclamationId', $typeReclamationId);
    }

    return $qb->getQuery()->getResult();
}
    //    /**
    //     * @return Reclamation[] Returns an array of Reclamation objects
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

    //    public function findOneBySomeField($value): ?Reclamation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
