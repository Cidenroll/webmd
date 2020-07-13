<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Model\UserRegistrationFormModel;
use App\Form\UserRegistrationFormType;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use App\Services\LogAnalyticsService;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     * @param LogAnalyticsService $analytics
     */
    public function logout(LogAnalyticsService $analytics): void
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/register", name="app_register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $formAuthenticator
     * @return Response|null
     */
    public function register(Request $request,
                             UserPasswordEncoderInterface $passwordEncoder,
                             GuardAuthenticatorHandler $guardHandler,
                             LoginFormAuthenticator $formAuthenticator): ?Response
    {
        $form = $this->createForm(UserRegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UserRepository $userRepository */
            $userRepository = $this->getDoctrine()->getRepository(User::class);
            $userCount = $userRepository->getUserCount();

            /** @var UserRegistrationFormModel $userModel */
            $userModel = $form->getData();

            switch ($userModel->userType) {
                case 'patient':
                    $patientCount = $userRepository->getPatientCount();
                    if ($patientCount >= $this->getParameter('patient_limit')) {
                        return $this->render('security/register.html.twig', [
                            'maxNumberOfUsers' => 'Limit of registered patients passed. Unable to register more patients.',
                            'registrationForm' => $form->createView()
                        ]);
                    }

                    break;
                case 'doctor':
                    $doctorCount = $userRepository->getDoctorCount();
                    if ($doctorCount >= $this->getParameter('doctor_limit')) {
                        return $this->render('security/register.html.twig', [
                            'maxNumberOfUsers' => 'Limit of registered doctors passed. Unable to register more doctors.',
                            'registrationForm' => $form->createView()
                        ]);
                    }
                    break;
            }

            $user = new User();
            $user->setEmail($userModel->email);
            $user->setPassword($passwordEncoder->encodePassword(
                $user,
                $userModel->plainPassword
            ));

            $user->setUserType($userModel->userType);
            $user->setTelephoneNumber($userModel->telephone);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();


            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $formAuthenticator,
                'main'
            );
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/terms", name="viewT&C")
     * @param LogAnalyticsService $analytics
     * @return Response
     */
    public function viewTerms(LogAnalyticsService $analytics): Response
    {
        return $this->render(
            'terms.html.twig', []
        );
    }


}
