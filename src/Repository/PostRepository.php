<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

class PostRepository extends ServiceEntityRepository
{    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

public function findByCategoryAndTypeAndEnabled(?int $categoryId = null, ?string $type = null, string $sortBy = 'newest'): Query
{

    $queryBuilder = $this->createQueryBuilder('p')
        ->leftJoin('p.comments', 'c') // Join the comments table for sorting by number of comments
        ->andWhere('p.enabled = :enabled')
        ->setParameter('enabled', true)
        ->groupBy('p.id'); // Group by post ID to avoid duplicates

    // Filter by category if provided
    if ($categoryId) {
        $queryBuilder
            ->andWhere('p.category = :categoryId')
            ->setParameter('categoryId', $categoryId);
    }

    // Filter by type if provided
    if ($type) {
        $queryBuilder
            ->andWhere('p.type = :type')
            ->setParameter('type', $type);
    }

    // Apply sorting based on the selected option
    switch ($sortBy) {
        case 'newest':
            $queryBuilder->orderBy('p.createdAt', 'DESC');
            break;
        case 'oldest':
            $queryBuilder->orderBy('p.createdAt', 'ASC');
            break;
        case 'most_comments':
            $queryBuilder->orderBy('COUNT(c.id)', 'DESC');
            break;
        case 'least_comments':
            $queryBuilder->orderBy('COUNT(c.id)', 'ASC');
            break;
        default:
            $queryBuilder->orderBy('p.createdAt', 'DESC'); // Default to newest first
    }    

    return $queryBuilder->getQuery();
}

    public function findUniqueTypes(): array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT p.type')
            ->orderBy('p.type', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();  
    }

    public function findFilteredAndSortedPosts(?int $categoryId = null, ?string $type = null, string $sortBy = 'newest')
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.comments', 'c') // Join the comments table for sorting by number of comments
            ->groupBy('p.id'); // Group by post ID to avoid duplicates
    
        // Filter by category if provided
        if ($categoryId) {
            $queryBuilder
                ->andWhere('p.category = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }
    
        // Filter by type if provided
        if ($type) {
            $queryBuilder
                ->andWhere('p.type = :type')
                ->setParameter('type', $type);
        }
    
        // Apply sorting based on the selected option
        switch ($sortBy) {
            case 'newest':
                $queryBuilder->orderBy('p.createdAt', 'DESC');
                break;
            case 'oldest':
                $queryBuilder->orderBy('p.createdAt', 'ASC');
                break;
            case 'most_comments':
                $queryBuilder->orderBy('COUNT(c.id)', 'DESC');
                break;
            case 'least_comments':
                $queryBuilder->orderBy('COUNT(c.id)', 'ASC');
                break;
            default:
                $queryBuilder->orderBy('p.createdAt', 'DESC'); // Default to newest first
        }
    
        return $queryBuilder->getQuery()->getResult();
    }

}

    //    /**
    //     * @return Post[] Returns an array of Post objects
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

    //    public function findOneBySomeField($value): ?Post
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }