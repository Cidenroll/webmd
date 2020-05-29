<?php

namespace App\Repository;

use App\Entity\ProcessedFiles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProcessedFiles|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProcessedFiles|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProcessedFiles[]    findAll()
 * @method ProcessedFiles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProcessedFilesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProcessedFiles::class);
    }

//    // /**
//    //  * @return ProcessedFiles[] Returns an array of ProcessedFiles objects
//    //  */
//    /*
    public function findProcessedFileByFileID($fileID)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.fileId = :val')
            ->setParameter('val', $fileID)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?ProcessedFiles
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
