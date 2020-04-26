<?php

namespace App\Repository;

use App\Entity\DoctorToPatient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Controller\UserValueResolver;

/**
 * @method DoctorToPatient|null find($id, $lockMode = null, $lockVersion = null)
 * @method DoctorToPatient|null findOneBy(array $criteria, array $orderBy = null)
 * @method DoctorToPatient[]    findAll()
 * @method DoctorToPatient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DoctorToPatientRepository extends ServiceEntityRepository
{
    /**
     * @var ManagerRegistry
     */
    private $registry;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(ManagerRegistry $registry, UserRepository $userRepository)
    {
        parent::__construct($registry, DoctorToPatient::class);
        $this->registry = $registry;
        $this->userRepository = $userRepository;
    }



    public function findAllRelationsToPatientsByDoctorId($docId)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.doctorId = :val')
            ->setParameter('val', $docId)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * A list of patients that can be freely taken by the doctor
     * @param $docId
     * @return array
     */
    public function getRemainingAvailablePatientsForDoctor($docId)
    {
        $patientsList = $this->findAllRelationsToPatientsByDoctorId($docId);
        // find all users of type patient
        $allPatients = $this->userRepository->findAllPatients();

        /** @var DoctorToPatient $patient */
        foreach ($patientsList as $patient) {
            dump($patient->getId());
        }

        //dd($patientsList, $allPatients);

        $getDiffPatientList = $allPatients;

        $patientsResult = [];
        foreach ($getDiffPatientList as $patient) {
            $patientsResult[$patient->getEmail()] = $patient;
        }

        return $patientsResult;
    }



}
