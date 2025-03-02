<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

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
            $qb->join('r.medecin', 'm')
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
}