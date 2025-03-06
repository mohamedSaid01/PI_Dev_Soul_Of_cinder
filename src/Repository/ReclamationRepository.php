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
