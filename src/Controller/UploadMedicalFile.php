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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function upload(Request $request)
    {
        $form = $this->createForm(UserFileFormType::class);
        $form->handleRequest($request);

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $pdfFile */
            $pdfFile = $form->get('fileName')->getData();

            if ($pdfFile) {
                $originalFileName = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFileName = $currentUser->getId().'_'.$originalFileName.".".$pdfFile->guessExtension();

                // Move the file to the directory where pdfs are stored
                try {
                    $pdfFile->move(
                        $this->getParameter('pdf_directory'),
                        $newFileName
                    );
                } catch (FileException $e) {
                    return $this->redirect($this->generateUrl('uploadfile'), 404);
                }

                $em = $this->getDoctrine()->getManager();
                $userFileEnt = new UserFile();
                $userFileEnt->setFileName($newFileName);
                $userFileEnt->setDocType($form->get('docType')->getData());

                /** @var UploadedFile $uploadedFile */
                $userFileEnt->setFileContent(base64_encode($form->get('fileName')->getData()));
                $userFileEnt->setUserId($currentUser);

                $em->persist($userFileEnt);
                $em->flush();
            }

            return $this->redirect($this->generateUrl('homepage', [
                'userFile'  =>  $userFileEnt
            ]));

        }


        return $this->render('upload/mdfile.html.twig', [
            'medicalFileForm'   =>  $form->createView(),
        ]);
    }

}