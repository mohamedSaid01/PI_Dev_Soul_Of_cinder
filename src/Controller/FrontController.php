<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FrontController extends AbstractController
{
    #[Route('/', name: 'display_front')]
    public function index(): Response
    {
        // if ($this->getUser()){
        //     dd($this->getUser()->getFirstName());
        // }
        return $this->render('front/index.html.twig'
        );
    }
}
