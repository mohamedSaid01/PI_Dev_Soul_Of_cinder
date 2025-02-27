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
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->andWhere('u.isVerified = :isVerified')
            ->setParameter('role', '%"' . $role . '"%')
            ->setParameter('isVerified', true)
            ->getQuery()
            ->getResult();
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
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.roles LIKE :role')
            ->andWhere('u.isVerified = :isVerified') // Ajouter la condition isVerified = 1
            ->setParameter('role', '%"' . $role . '"%')
            ->setParameter('isVerified', true) // ou 1, selon le type de votre champ isVerified
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function findAgesByRole(string $role): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('age', 'age');

        $query = $this->getEntityManager()->createNativeQuery('
            SELECT u.age
            FROM user u
            WHERE u.roles LIKE :role
            AND u.is_verified = 1
        ', $rsm);

        $query->setParameter('role', '%"' . $role . '"%');

        return $query->getResult();
    }

    public function countByRoleAndGender(string $role, string $gender): int
{
    return $this->createQueryBuilder('u')
        ->select('COUNT(u.id)')
        ->where('u.roles LIKE :role')
        ->andWhere('u.isVerified = :isVerified')
        ->andWhere('u.gender = :gender')
        ->setParameter('role', '%"' . $role . '"%')
        ->setParameter('isVerified', true)
        ->setParameter('gender', $gender)
        ->getQuery()
        ->getSingleScalarResult();
}

public function findByNomPrenom(string $searchTerm): array
{
    return $this->createQueryBuilder('p')
        ->where('p.lastName LIKE :searchTerm OR p.firstName LIKE :searchTerm')
        ->setParameter('searchTerm', '%' . $searchTerm . '%')
        ->getQuery()
        ->getResult();
}

}
