<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\ORM\Query\ResultSetMapping;


/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Trouve les utilisateurs ayant un rôle spécifique.
     *
     * @param string $role
     * @return User[]
     */
    public function findByRole(string $role): array
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->andWhere('u.isVerified = :isVerified')
            ->setParameter('role', '%"' . $role . '"%')
            ->setParameter('isVerified', true);
    
        // Appliquer la condition sur le statut uniquement pour les médecins
        if ($role === 'ROLE_MEDECIN') {
            $queryBuilder->andWhere('u.status = :status')
                ->setParameter('status', 'verifie');
        }
    
        return $queryBuilder->getQuery()->getResult();
    }


    public function countUserInscriptions(User $user): int
    {
        return $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }




    public function countByRole(string $role): int
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.roles LIKE :role')
            ->andWhere('u.isVerified = :isVerified')
            ->setParameter('role', '%"' . $role . '"%')
            ->setParameter('isVerified', true);
    
        // Appliquer la condition sur le statut uniquement pour les médecins
        if ($role === 'ROLE_MEDECIN') {
            $queryBuilder->andWhere('u.status = :status')
                ->setParameter('status', 'verifie');
        }
    
        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    public function findAgesByRole(string $role): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('age', 'age');
    
        $sql = '
            SELECT u.age
            FROM user u
            WHERE u.roles LIKE :role
            AND u.is_verified = 1
        ';
    
        // Ajouter la condition sur le statut uniquement pour les médecins
        if ($role === 'ROLE_MEDECIN') {
            $sql .= ' AND u.status = :status';
        }
    
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('role', '%"' . $role . '"%');
    
        // Ajouter le paramètre status uniquement pour les médecins
        if ($role === 'ROLE_MEDECIN') {
            $query->setParameter('status', 'verifie');
        }
    
        return $query->getResult();
    }


    public function countByRoleAndGender(string $role, string $gender): int
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.roles LIKE :role')
            ->andWhere('u.isVerified = :isVerified')
            ->andWhere('u.gender = :gender')
            ->setParameter('role', '%"' . $role . '"%')
            ->setParameter('isVerified', true)
            ->setParameter('gender', $gender);
    
        // Appliquer la condition sur le statut uniquement pour les médecins
        if ($role === 'ROLE_MEDECIN') {
            $queryBuilder->andWhere('u.status = :status')
                ->setParameter('status', 'verifie');
        }
    
        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

public function findByNomPrenom(string $searchTerm): array
{
    return $this->createQueryBuilder('p')
        ->where('p.lastName LIKE :searchTerm OR p.firstName LIKE :searchTerm')
        ->setParameter('searchTerm', '%' . $searchTerm . '%')
        ->getQuery()
        ->getResult();
}


public function findBySpecialite(string $specialite): array
{
    return $this->createQueryBuilder('u')
        ->where('u.specialite = :specialite') // Filtre directement sur la valeur de l'enum
        ->setParameter('specialite', $specialite)
        ->getQuery()
        ->getResult();
}


}



