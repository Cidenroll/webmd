<?php
/**
 * Created by PhpStorm.
 * User: Delz
 * Date: 4/25/2020
 * Time: 6:40 PM
 */

namespace App\Controller;


use App\Repository\UserFileRepository;
use App\Repository\UserRepository;
use App\Services\SMTPeter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
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
     * @param TexterInterface $textInterface
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\Notifier\Exception\TransportExceptionInterface
     */
    public function  submitPatientForm(Request $request, MailerInterface $mailer)
    {
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


            if ($checkMail) {

                $docEntity = $this->userRepository->find($docId);
                $doctorTelephone = $docEntity->getTelephoneNumber();

                /** @var User $currentPatient */
                $currentPatient = $this->security->getUser();

                if ($doctorTelephone) {

                    $basic  = new \Nexmo\Client\Credentials\Basic('68fd2098', 'mhtjTAOl5c0tpML5');
                    $client = new \Nexmo\Client($basic);

                    $message = $client->message()->send([
                        'to' => "+4".$doctorTelephone,
                        'from' => 'MSING-SDM',
                        'text' => sprintf("Hello doctor %s! The pacient %s has submitted a file to you. Please log in to your MSING-SDM application to view the files and diagnosis.", $docEntity->getEmail(), $currentPatient->getEmail())
                    ]);

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