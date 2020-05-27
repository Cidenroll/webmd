<?php
/**
 * Created by PhpStorm.
 * User: Delz
 * Date: 4/25/2020
 * Time: 5:08 PM
 */

namespace App\Controller;


use App\Services\LogAnalyticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class NotFoundController extends AbstractController
{

    /**
     * @Route("/notfound", name="notFound")
     * @param LogAnalyticsService $analytics
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function NotFoundIndex(LogAnalyticsService $analytics)
    {
        return $this->render('notfound.html.twig',[]);
    }
}