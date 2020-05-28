<?php
/**
 * Created by PhpStorm.
 * User: Delz
 * Date: 4/25/2020
 * Time: 2:01 AM
 */

namespace App\Controller;




use App\Entity\RelationsDp2;
use App\Entity\User;
use App\Entity\UserFile;
use App\Form\DoctorPatientFormType;
use App\Form\UserFileCKFormType;
use App\Form\UserFileFormType;
use App\Repository\RelationsDp2Repository;
use App\Repository\UserFileRepository;
use App\Repository\UserRepository;
use App\Services\LogAnalyticsService;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class DoctorRelationController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var RelationsDp2Repository $doctorToPatient
     */
    private $reld2prepo;
    /**
     * @var UserFileRepository
     */
    private $userFileRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;


    public function __construct(Security $security,  RelationsDp2Repository $relationsDp2Repository, UserFileRepository $userFileRepository, UserRepository $userRepository)
    {
        $this->security = $security;
        $this->reld2prepo = $relationsDp2Repository;
        $this->userFileRepository = $userFileRepository;
        $this->userRepository = $userRepository;
        $currentUser = $this->security->getUser();
    }

    /**
     * @Route("/md", name="medAccount")
     * @param LogAnalyticsService $analytics
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function getPatientsList(LogAnalyticsService $analytics, Request $request)
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        if (!$currentUser) {
            return $this->redirect($this->generateUrl('homepage'));
        }
        if ($currentUser->getUserType() != 'doctor') {
            return $this->redirect($this->generateUrl('homepage'), 404);
        }

        $form = $this->createForm(DoctorPatientFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $patient = $form->get('patientId')->getData();
            if ($patient) {
                $em = $this->getDoctrine()->getManager();
                $docToPatient = new RelationsDp2();
                $docToPatient->setDoctorId($currentUser->getId());
                $docToPatient->setPatientId($patient);

                $em->persist($docToPatient);
                $em->flush();

               return $this->redirect($this->generateUrl('medAccount'));
            }
            else {
                return $this->redirect($this->generateUrl('notFound'));
            }
        }

        $fileDetailsList = [];

        $allPatientsForDoctorList = $this->reld2prepo->findAllRelationsToPatientsByDoctorId($currentUser->getId());

        /** @var User $patient */
        foreach ($allPatientsForDoctorList as $patient) {
            $filesAssignedToDoctor = $this->userFileRepository->findNumberOfFilesAssignedToDoctor($patient->getId(), $currentUser->getId());
            $fileDetailsList[$patient->getEmail()]['count'] = $filesAssignedToDoctor;
            $fileDetailsList[$patient->getEmail()]['id'] = $patient->getId();
            $fileDetailsList[$patient->getEmail()]['profile'] = $patient->getProfilePicturePath();
        }


        return $this->render('medAccount/medacc.html.twig', [
            'docToPatientForm' => $form->createView(),
            'patientList'   =>  $fileDetailsList,
            'remainingPatients' => count($this->reld2prepo->getRemainingAvailablePatientsForDoctor($currentUser->getId()))
        ]);

    }


    /**
     * @Route("/md/{id}", name="medDetails")
     * @param $id
     * @param LogAnalyticsService $analytics
     * @return RedirectResponse|Response
     */
    public function getMedicalDocsFromPatient($id, LogAnalyticsService $analytics)
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            return $this->redirect($this->generateUrl('homepage'));
        }
        $filesAssignedToDoctor = $this->userFileRepository->findFilesAssignedToDoctor($id, $currentUser->getId());

        /** @var UserFile $getUserFile */
        $getUserFile = $filesAssignedToDoctor[0];
        $userMail = $getUserFile->getUserId()->getEmail();


        $files = [];
        /** @var UserFile $file */
        foreach ($filesAssignedToDoctor as $file) {
            $files[$file->getId()] = [
                'docType'   => $file->getDocType(),
                'fileName'  =>  $file->getFileName(),
                'comment'   =>  $file->getComment()
            ];
        }

        return $this->render('medAccount/meddetails.html.twig', [
            'files' =>  $files,
            'patient'   => $userMail
        ]);
    }

    /**
     * @Route("/md/{id}/{file}/comment", name="medicalComment")
     * @param $id
     * @param $file
     * @param Request $request
     * @param LogAnalyticsService $analytics
     * @return RedirectResponse|Response
     */
    public function commentFormForDoctor($id, $file, Request $request, LogAnalyticsService $analytics)
    {
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        $user = $this->userRepository->find($id);
        $userMail = $user->getEmail();

        $userFile = $this->userFileRepository->find($file);

        if ($request->getMethod() == 'POST') {
           $comment = urldecode(filter_var(strip_tags(htmlspecialchars($request->get('comment'))), FILTER_SANITIZE_FULL_SPECIAL_CHARS));
           $comment = trim($comment);

           $userFile->setComment($comment);
           $em = $this->getDoctrine()->getManager();
           $em->flush();



           return $this->redirect($this->generateUrl('medDetails', ['id'   =>  $id]));

        }


        return $this->render('medAccount/medcomment.html.twig', [
            'userMail'  =>  $userMail,
            'comment'   =>  $userFile->getComment(),
            'fileName'  =>  $userFile->getFileName()
        ]);
    }

    /**
     * TODO: to be continued in a future run
     * @Route("/md/{id}/{file}/ckComment", name="medicalCommentCK")
     * @param $id
     * @param $file
     * @param Request $request
     * @param LogAnalyticsService $analytics
     * @return Response
     */
    public function newcommentFormForDoctor($id, $file, Request $request, LogAnalyticsService $analytics)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var UserFile $userFileEnt */
        $userFileEnt = $this->getDoctrine()->getRepository(UserFile::class)->find($file);

        $defaultData = ['userFileCK' => $userFileEnt->getComment()];
        $form = $this->createFormBuilder()
            ->add('userFileCK', CKEditorType::class, [
                'label' => "Comments",
                'mapped' => false,
                'config' => [
                    "uiColor" => "#ffffff",
                    'toolbar' => 'standard'
                ],
                'data' => $userFileEnt->getComment()
            ])
            ->add('save', SubmitType::class, [
                'attr' => ["class" => "btn btn-primary btn-block"]
            ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $commentData = $form->get('userFileCK')->getData();
            if ($commentData) {
                $userFileEnt->setComment($commentData);
                $em->persist($userFileEnt);
                $em->flush();
            }
            return $this->redirect($this->generateUrl('medDetails', ['id'   =>  $id]));
        }

        return $this->render('medAccount/medaccck.html.twig', [
            'docToPatientForm' => $form->createView(),
        ]);
    }
}