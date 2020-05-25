<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\UserProfileFormType;
use App\Services\ProfileUploaderHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class UploadUserProfileController extends AbstractController
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
     * @Route("/{id}/profile", name="upload_profile_pic")
     * @param $id
     * @param Request $request
     * @param ProfileUploaderHelper $uploaderHelper
     * @return Response
     */
    public function uploadProfile($id, Request $request, ProfileUploaderHelper $uploaderHelper)
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $form = $this->createForm(UserProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            /** @var UploadedFile $pdfFile */
            $profilePic = $form->get('profilePicturePath')->getData();

            if ($profilePic) {
                if ($result = $uploaderHelper->upload($profilePic, $currentUser->getId())) {

                    $em = $this->getDoctrine()->getManager();
                    /** @var User $user */
                    $user->setProfilePicturePath($result['newFileName']);
                    $em->persist($user);
                    $em->flush();

                    return $this->redirect($this->generateUrl('homepage'));
                }
            }
        }


        return $this->render('upload/profilepic.html.twig', [
            'profilePicForm'   =>  $form->createView(),
        ]);
    }

}