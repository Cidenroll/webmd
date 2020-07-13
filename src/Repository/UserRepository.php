<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use function get_class;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }


    public function findAllPatients()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.userType = :val')
            ->setParameter('val', 'patient')
            ->orderBy('u.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllDoctors()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.userType = :val')
            ->setParameter('val', 'doctor')
            ->orderBy('u.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function getUserCount()
    {
        $allUsers = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
        return count($allUsers);
    }

    public function getDoctorCount(): int
    {
        $allUsers = $this->createQueryBuilder('u')
            ->where("u.userType = 'doctor'")
            ->getQuery()
            ->getResult()
        ;
        return count($allUsers);
    }

    public function getPatientCount(): int
    {
        $allUsers = $this->createQueryBuilder('u')
            ->where("u.userType='patient'")
            ->getQuery()
            ->getResult()
        ;
        return count($allUsers);
    }

}
