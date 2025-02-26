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
public function searchReclamations(string $query, ?int $limit = null, ?int $offset = null): array
{
    return $this->createQueryBuilder('r')
        ->leftJoin('r.medecin', 'm') // Ensure "medecin" matches the property name in Reclamation
        ->where('r.description LIKE :query')
        ->orWhere('m.nom LIKE :query') // Ensure "m" is the alias for Medecin
        ->setParameter('query', '%' . $query . '%')
        ->setMaxResults($limit)
        ->setFirstResult($offset)
        ->getQuery()
        ->getResult();
}

public function findByType(string $typeReclamation): array
{
    return $this->createQueryBuilder('r')
        ->andWhere('r.typereclamation = :typereclamation')
        ->setParameter('typereclamation', $typeReclamation)
        ->getQuery()
        ->getResult();
}


public function findByFilters(?string $description, ?string $medecin, ?int $typeReclamationId): array
{
    $qb = $this->createQueryBuilder('r');

    if ($description) {
        $qb->andWhere('r.description LIKE :description')
           ->setParameter('description', '%' . $description . '%');
    }

    if ($medecin) {
        $qb->join('r.medecin', 'm')
           ->andWhere('m.nom LIKE :medecin')
           ->setParameter('medecin', '%' . $medecin . '%');
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
