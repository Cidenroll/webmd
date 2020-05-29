<?php
/**
 * Created by PhpStorm.
 * User: Delz
 * Date: 4/25/2020
 * Time: 6:40 PM
 */

namespace App\Controller;


use App\Entity\ProcessedFiles;
use App\Entity\UserFile;
use App\Repository\UserFileRepository;
use App\Repository\UserRepository;
use App\Services\LogAnalyticsService;
use Nexmo\Client\Exception\Server;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class SubmitFormFromPatientController extends AbstractController
{

    /**
     * @var Security
     */
    private $security;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var UserFileRepository
     */
    private $userFileRepository;


    public function __construct(Security $security, UserRepository $userRepository, UserFileRepository $userFileRepository)
    {
        $this->security = $security;
        $this->userRepository = $userRepository;
        $this->userFileRepository = $userFileRepository;

    }

    /**
     * @Route("/patientFormSubmit", name="patientFormSubmit")
     * @param Request $request
     * @param MailerInterface $mailer
     * @param LogAnalyticsService $analytics
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function  submitPatientForm(Request $request, MailerInterface $mailer, LogAnalyticsService $analytics)
    {
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            return $this->redirect($this->generateUrl('homepage'));
        }
        if ($request->getMethod() == 'POST'){

            $email = $request->get('email');
            $sex = $request->get('sex');
            $cnp = $request->get('cnp');
            $age = $request->get('age');
            $institute = $request->get('institution');
            $dates = $request->get('dates');
            $diagnostic = $request->get('diagnostic');
            $resultSummary = $request->get('resultSummary');
            $docId = $request->get('doctor');
            $checkMail = $request->get('checkMail');
            $userFileId = $request->get('userFileId');

            /** @var UserFile $userFile */
            $userFile = $this->userFileRepository->find($userFileId);
            $lastCommentingDoctorId =  $userFile->getLatestCommentedDoctorID()?:"";

            $content = [
                'docId'         => $docId,
                'reminder'     => $checkMail,
                'email' => $email,
                'sex'   => $sex,
                'cnp'   => $cnp,
                'age'   => $age,
                'institute' => $institute,
                'dates'     => $dates,
                'diagnostic'=> $diagnostic,
                'resultSummary' => $resultSummary,
            ];

            $em = $this->getDoctrine()->getManager();
            $resultsProcessedFile = $em->getRepository(ProcessedFiles::class)->findProcessedFileByFileID($userFileId);

            /** @var ProcessedFiles $currentProcessedFile */
            if (!empty($resultsProcessedFile)) {
                $currentProcessedFile = $resultsProcessedFile[0];
                $currentProcessedFile->setUpdatedAt(new \DateTime());
                $currentProcessedFile->setLastCommentingDoctorId((int)$lastCommentingDoctorId);
                $currentProcessedFile->setPatientId($currentUser->getId());
                $currentProcessedFile->setContent(json_encode($content));
                $em->persist($currentProcessedFile);
                $em->flush();
            }
            else {
                $processedFile = new ProcessedFiles();
                $processedFile->setFileId($userFileId);
                $processedFile->setLastCommentingDoctorId((int)$lastCommentingDoctorId);
                $processedFile->setPatientId($currentUser->getId());
                $processedFile->setContent(json_encode($content));
                $em->persist($processedFile);
                $em->flush();
            }



            if ($checkMail) {

                $docEntity = $this->userRepository->find($docId);
                $doctorTelephone = $docEntity->getTelephoneNumber();

                /** @var User $currentPatient */
                $currentPatient = $this->security->getUser();

                if ($doctorTelephone) {

                    $basic  = new \Nexmo\Client\Credentials\Basic('68fd2098', 'mhtjTAOl5c0tpML5');
                    $client = new \Nexmo\Client($basic);

                    try {
                        $message = $client->message()->send(['to' => "+4" . $doctorTelephone, 'from' => 'MSING-SDM', 'text' => sprintf("Hello doctor %s! The pacient %s has submitted a file to you. Please log in to your MSING-SDM application to view the files and diagnosis.", $docEntity->getEmail(), $currentPatient->getEmail())]);
                    } catch (\Nexmo\Client\Exception\Request $e) {
                    } catch (Server $e) {
                    }
                }
            }

            // UPDATE USER FILE WITH THE CORRECT DOCTOR ID
            if ($userFile = $this->userFileRepository->find($userFileId)) {
                $userFile->setDoctorId($docId);
                $em = $this->getDoctrine()->getManager();
                $em->flush();

                return $this->redirect($this->generateUrl('patAccount'));

            }
            else {
                throw $this->createNotFoundException('No user file found for the sent user file id.');
            }
        }

        throw $this->createNotFoundException('POST method missing.');

    }


}