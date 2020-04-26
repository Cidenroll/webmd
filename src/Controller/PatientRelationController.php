<?php
/**
 * Created by PhpStorm.
 * User: Delz
 * Date: 4/25/2020
 * Time: 4:43 PM
 */

namespace App\Controller;


use App\Entity\RelationsPd2;
use App\Form\PatientDoctorFormType;
use App\Repository\RelationsPd2Repository;

use App\Repository\UserFileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class PatientRelationController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var RelationsPd2Repository
     */
    private $pd2Repository;
    /**
     * @var UserFileRepository
     */
    private $userFileRepository;

    public function __construct(Security $security, RelationsPd2Repository $pd2Repository, UserFileRepository $userFileRepository)
    {
        $this->security = $security;
        $this->pd2Repository = $pd2Repository;
        $this->userFileRepository = $userFileRepository;
    }

    /**
     * @Route("/pa/", name="patAccount")
     */
    public function patientAccountController(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        if ($currentUser->getUserType() != 'patient') {
            return $this->redirect($this->generateUrl('homepage'), 404);
        }

        $form = $this->createForm(PatientDoctorFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctor = $form->get('doctorId')->getData();
            if ($doctor) {

                $em = $this->getDoctrine()->getManager();
                $patientToDoctor = new RelationsPd2();
                $patientToDoctor->setPatientId($currentUser->getId());
                $patientToDoctor->setDoctorId($doctor);

                $em->persist($patientToDoctor);
                $em->flush();

                return $this->redirect($this->generateUrl('patAccount'));
            }
            else {
                return $this->redirect($this->generateUrl('notFound'));
            }
        }

        $doctorDetails = [];
        $doctorsList = $this->pd2Repository->findAllRelationsToDoctorsByPatientId($currentUser->getId());
        foreach ($doctorsList as $doctor) {
            $doctorDetails[$doctor->getEmail()] = $this->getAllFilesSubmittedForPatientByDocID($doctor->getId());
        }


        return $this->render('medAccount/patacc.html.twig', [

            'PatientToDocForm' => $form->createView(),
            'docsList'   => $doctorDetails,
        ]);
    }

    private function getAllFilesSubmittedForPatientByDocID($docId)
    {
        $currentUserId = $this->security->getUser()->getId();
        $numberOFFiles = $this->userFileRepository->findNumberOfFilesAssignedToDoctor($currentUserId, $docId);

        return $numberOFFiles;
    }


}