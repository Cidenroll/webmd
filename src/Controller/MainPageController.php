<?php
/**
 * Created by PhpStorm.
 * User: Delz
 * Date: 4/23/2020
 * Time: 8:03 PM
 */

namespace App\Controller;


use App\Entity\UserFile;
use App\Repository\UserFileRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class MainPageController extends AbstractController
{
    /**
     * @var UserFileRepository
     */
    private $userFileRepository;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * MainPageController constructor.
     * @param UserFileRepository $userFileRepository
     */
    public function __construct(UserFileRepository $userFileRepository, Security $security, UserRepository $userRepository)
    {
        $this->userFileRepository = $userFileRepository;
        $this->security = $security;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function homepage()
    {
        $currentUser = $this->security->getUser();
        if ($currentUser) {

            $userFiles = $this->userFileRepository->findByUserId($currentUser->getId());


            $ufList = [];
            /** @var UserFile $userFile */
            foreach ($userFiles as $userFile) {

                $doctorMail ='';
                if ($userFile->getDoctorId()) {
                    $doctorEnt = $this->userRepository->find($userFile->getDoctorId());
                    if ($doctorEnt) {
                        $doctorMail = $doctorEnt->getEmail();
                    }
                }


                $ufList[$userFile->getId()] = [
                    'fileName'  =>  $userFile->getFileName(),
                    'doctorMail'    =>  $doctorMail,
                    'docType'  =>  $userFile->getDocType(),
                    'comment'   =>  $userFile->getComment(),
                ];
            }


            return $this->render('homepage/homepage.html.twig', [
                'userFiles' =>  $ufList
            ]);
        }
        else {
            return $this->render('homepage/homepage.html.twig', [
            ]);
        }

    }
}