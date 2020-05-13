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
use Symfony\Component\HttpFoundation\Response;
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
     * @param Security $security
     * @param UserRepository $userRepository
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
    public function homepage(): ?Response
    {
        $currentUser = $this->security->getUser();
        $em = $this->getDoctrine()->getManager();
        if ($currentUser) {

            $userFiles = $this->userFileRepository->findByUserId($currentUser->getId());

            // check all paths of files
            /** @var UserFile $userFile */
            foreach ($userFiles as $userFile) {
                if (!file_exists($this->getParameter('pdf_directory').'/'.$userFile->getFileName())) {
                    $em->remove($userFile);
                }
            }
            $em->flush();

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
                    'fileContent'   =>  $userFile->getFileContent()
                ];
            }


            return $this->render('homepage/homepage.html.twig', [
                'userFiles' =>  $ufList
            ]);
        }

        return $this->render('homepage/homepage.html.twig', [
        ]);

    }
}