<?php

namespace App\Repository;

use App\Entity\RelationsDp2;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RelationsDp2|null find($id, $lockMode = null, $lockVersion = null)
 * @method RelationsDp2|null findOneBy(array $criteria, array $orderBy = null)
 * @method RelationsDp2[]    findAll()
 * @method RelationsDp2[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RelationsDp2Repository extends ServiceEntityRepository
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(ManagerRegistry $registry, UserRepository $userRepository)
    {
        parent::__construct($registry, RelationsDp2::class);
        $this->userRepository = $userRepository;
    }


    public function findAllRelationsToPatientsByDoctorId($docId)
    {
        $allPatients = $this->createQueryBuilder('d')
            ->andWhere('d.doctorId = :val')
            ->setParameter('val', $docId)
            ->getQuery()
            ->getResult();

        $patientCollection = [];

        foreach ($allPatients as $patient) {
            $patientCollection[] = $this->userRepository->find($patient->getPatientId());
        }

        return $patientCollection;

    }

    /**
     * A list of patients that can be freely taken by the doctor
     * @param $docId
     * @return array
     */
    public function getRemainingAvailablePatientsForDoctor($docId)
    {
        $patientsListNew = $allPatientsNew = [];

        $relationsD2P = $this->findAllRelationsToPatientsByDoctorId($docId);
        foreach ($relationsD2P as $relation) {
            $patientsListNew[$relation->getId()] = $relation->getId();
        }

        // find all users of type patient
        $allPatients = $this->userRepository->findAllPatients();
        foreach ($allPatients as $patient) {
            $allPatientsNew[$patient->getId()] = $patient->getId();
        }

        $getDiffPatientList = array_diff($allPatientsNew, $patientsListNew);

        $patientsResult = [];
        foreach ($getDiffPatientList as $patientId) {
            $user = $this->userRepository->find($patientId);
            $patientsResult[$user->getEmail()] = $user->getId();
        }

        return $patientsResult;
    }
}
