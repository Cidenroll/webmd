<?php

namespace App\Repository;

use App\Entity\MapLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MapLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method MapLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method MapLog[]    findAll()
 * @method MapLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MapLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MapLog::class);
    }

    // /**
    //  * @return MapLog[] Returns an array of MapLog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MapLog
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
