<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.isArchived = :archived')
            ->setParameter('archived', false)
            ->getQuery()
            ->getResult();
    }

    public function findAllArchived(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.isArchived = :archived')
            ->setParameter('archived', true)
            ->getQuery()
            ->getResult();
    }

    public function findExpiredEvents(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.isArchived = false') // Seulement les événements non archivés
            ->andWhere('e.endDate < :now') // Date de fin dépassée
            ->setParameter('now', new \DateTime()) // Date actuelle
            ->getQuery()
            ->getResult();
    }

    public function searchEvents(?string $title, ?string $startDate, ?int $categorieId, bool $isArchived)
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.isArchived = :isArchived') // ✅ Filtre bien actifs ou archivés
            ->setParameter('isArchived', $isArchived);

        if ($title) {
            $qb->andWhere('e.title LIKE :title')
                ->setParameter('title', '%' . $title . '%');
        }

        if ($startDate) {
            $qb->andWhere('e.startDate >= :startDate')
                ->setParameter('startDate', new \DateTime($startDate));
        }

        if ($categorieId) {
            $qb->andWhere('e.categorie = :categorieId')
                ->setParameter('categorieId', $categorieId);
        }

        return $qb->getQuery()->getResult();
    }




    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
