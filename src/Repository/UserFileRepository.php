<?php

namespace App\Repository;

use App\Entity\UserFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFile[]    findAll()
 * @method UserFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFile::class);
    }

    // /**
    //  * @return UserFile[] Returns an array of UserFile objects
    //  */
    public function findByUserId($userId)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.userId = :val')
            ->setParameter('val', $userId)
            ->orderBy('u.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findDocTypeByFileId($fileId)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.id = :val')
            ->setParameter('val', $fileId)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    public function findNumberOfFilesAssignedToDoctor($patientId, $doctorId)
    {
        return $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->andWhere('u.userId = :val AND u.doctorId = :doc')
            ->setParameter('val', $patientId)
            ->setParameter('doc', $doctorId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findFilesAssignedToDoctor($patientId, $doctorId)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.userId = :val AND u.doctorId = :doc')
            ->setParameter('val', $patientId)
            ->setParameter('doc', $doctorId)
            ->getQuery()
            ->getResult();
    }

}
