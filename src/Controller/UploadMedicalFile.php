<?php
/**
 * Created by PhpStorm.
 * User: Delz
 * Date: 4/23/2020
 * Time: 9:51 PM
 */

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserFile;
use App\Form\UserFileFormType;
use App\Services\UploaderHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;


class UploadMedicalFile extends AbstractController
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/upload_md", name="uploadfile")
     * @param Request $request
     * @param UploaderHelper $uploaderHelper
     * @return RedirectResponse|Response
     */
    public function upload(Request $request, UploaderHelper $uploaderHelper)
    {
        $form = $this->createForm(UserFileFormType::class);
        $form->handleRequest($request);

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $pdfFile */
            $pdfFile = $form->get('fileName')->getData();

            if ($pdfFile) {
                if ($result = $uploaderHelper->upload($pdfFile, $currentUser->getId())) {

                    $em = $this->getDoctrine()->getManager();
                    $userFileEnt = new UserFile();
                    $userFileEnt->setFileName($result['newFileName']);
                    $userFileEnt->setDocType($form->get('docType')->getData());

                    #/** @var UploadedFile $uploadedFile */
                    $userFileEnt->setFileContent(base64_encode($form->get('fileName')->getData()));
                    $userFileEnt->setUserId($currentUser);
                    $em->persist($userFileEnt);
                    $em->flush();

                    return $this->redirect($this->generateUrl('homepage'));
                }
            }
        }

        return $this->render('upload/mdfile.html.twig', [
            'medicalFileForm'   =>  $form->createView(),
        ]);
    }
}