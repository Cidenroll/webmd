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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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

//            if ($checkMail) {
//
//                $email = (new Email())
//                    ->from('rusuflorinc@mexample.com')
//                    ->to('cromchardel@gmail.com')
//                    ->subject('Time for Symfony Mailer!')
//                    ->text('Sending emails is fun again!')
//                    ->html('<p>See Twig integration for better HTML integration!</p>');
//
//                $mailer->send($email);
//            }


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