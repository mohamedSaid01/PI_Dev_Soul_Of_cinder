<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Produit>
 *
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    /**
     * @param string $searchQuery
     * @return Produit[]
     */
    public function findByPriceRange($minPrice, $maxPrice)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.quantity > 0');
    
        if ($minPrice) {
            $qb->andWhere('p.price >= :minPrice')
               ->setParameter('minPrice', $minPrice);
        }
    
        if ($maxPrice) {
            $qb->andWhere('p.price <= :maxPrice')
               ->setParameter('maxPrice', $maxPrice);
        }
    
        return $qb->getQuery()->getResult();
    }

// src/Repository/ProduitRepository.php
public function findProduitBySearchQuery(string $query): array
{
    return $this->createQueryBuilder('p')
        ->where('p.name LIKE :query OR p.desciption LIKE :query')
        ->setParameter('query', '%' . $query . '%')
        ->getQuery()
        ->getResult();
}
// src/Repository/ProduitRepository.php
public function findMostOrderedCategories(): array
{
    return $this->createQueryBuilder('p')
        ->select('c.name, COUNT(cl.id) as total')
        ->leftJoin('p.commandeLignes', 'cl') // Use the correct association name
        ->leftJoin('p.Category', 'c') // Join with the Category entity
        ->groupBy('c.id') // Group by category ID
        ->orderBy('total', 'DESC') // Order by total orders descending
        ->setMaxResults(5) // Limit to top 5 categories
        ->getQuery()
        ->getResult();
dump($query->getSQL()); // Debug the SQL query
dump($query->getParameters()); // Debug the query parameters
}}

    //    /**
    //     * @return Produit[] Returns an array of Produit objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
