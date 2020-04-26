<?php
/**
 * Created by PhpStorm.
 * User: Delz
 * Date: 4/25/2020
 * Time: 5:08 PM
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class NotFoundController extends AbstractController
{

    /**
     * @Route("/notfound", name="notFound")
     */
    public function NotFoundIndex()
    {
        return $this->render('notfound.html.twig',[]);
    }
}