<?php

namespace App\Repository;

use App\Entity\RelationsPd2;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RelationsPd2|null find($id, $lockMode = null, $lockVersion = null)
 * @method RelationsPd2|null findOneBy(array $criteria, array $orderBy = null)
 * @method RelationsPd2[]    findAll()
 * @method RelationsPd2[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RelationsPd2Repository extends ServiceEntityRepository
{

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(ManagerRegistry $registry, UserRepository $userRepository)
    {
        parent::__construct($registry, RelationsPd2::class);

        $this->userRepository = $userRepository;
    }

    public function findAllRelationsToDoctorsByPatientId($patientId)
    {
        $allDoctors = $this->createQueryBuilder('d')
            ->andWhere('d.patientId = :val')
            ->setParameter('val', $patientId)
            ->getQuery()
            ->getResult();

        $patientCollection = [];

        foreach ($allDoctors as $doc) {
            $patientCollection[] = $this->userRepository->find($doc->getDoctorId());
        }

        return $patientCollection;

    }

    /**
     * A list of doctors that can be freely taken by the patient
     * @param $patientId
     * @return array
     */
    public function getRemainingAvailableDoctorsForPatient($patientId)
    {
        $doctorsListNew = $allDoctorsNew = [];

        // RETURNS USERS OF TYPE DOCTOR
        $relationsP2D = $this->findAllRelationsToDoctorsByPatientId($patientId);
        //dd($relationsP2D);
        foreach ($relationsP2D as $relation) {
            $doctorsListNew[$relation->getId()] = $relation->getId();
        }

        // find all users of type DOCTOR
        $allDoctors = $this->userRepository->findAllDoctors();

        foreach ($allDoctors as $doctor) {
            $allDoctorsNew[$doctor->getId()] = $doctor->getId();
        }



        $getDiffDoctorsList = array_diff($allDoctorsNew, $doctorsListNew);

        $doctorsResult = [];
        foreach ($getDiffDoctorsList as $docId) {
            $user = $this->userRepository->find($docId);
            $doctorsResult[$user->getEmail()] = $user->getId();
        }

        return $doctorsResult;
    }
}
