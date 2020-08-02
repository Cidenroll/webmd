<?php


namespace App\Services;


use App\Entity\Analytic;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;


class LogAnalyticsService
{
    /**
     * @var Security
     */
    private $security;

    private $currentUser;

    private $request;

    private $eventName = "";

    /** @var Request */
    private $currentRequest;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(Security $security, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->em = $em;
    }

    public function setCurrentUser(): void
    {
        $this->currentUser =  $this->security->getUser();
    }

    public function setCurrentUserFromExt($extUser): void
    {
        $this->currentUser =  $extUser;
    }


    /**
     * @param RequestStack $request
     */
    public function setCurrentRoute(RequestStack $request): void
    {
        $this->request = $request;
        $this->currentRequest = $this->request->getCurrentRequest();
    }

    /**
     * @return null|User
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    public function getCurrentRoute()
    {
        return $this->currentRequest;
    }

    public function getCurrentRouteParams()
    {
        return $this->currentRequest->attributes;
    }

    public function setEventName(string $eventName)
    {
        $this->eventName = $eventName;
    }

    public function postStatisticLog(): void
    {
        try {
            if ($params = $this->getCurrentRouteParams()) {
                if ($this->currentUser instanceof User) {
                    $analyticEnt = new Analytic();
                    $analyticEnt->setCurrentUserId($this->currentUser->getId());
                    $analyticEnt->setCurrentUserMail($this->currentUser->getEmail());
                    $analyticEnt->setCurrentRoute($params->get('_route')?:"");
                    $analyticEnt->setAction($params->get('_controller')?:"");
                    $analyticEnt->setUserRole($this->currentUser->getUserType()?:"-");
                    $analyticEnt->setCreatedAt(new \DateTime());
                    $analyticEnt->setUserTrace('action:'.($this->eventName));
                    $this->em->persist($analyticEnt);
                    $this->em->flush();
                }
            }
        }
        catch (\ErrorException $e)
        {

        }
    }
}