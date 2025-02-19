<?php

namespace App\Controller;

use App\Repository\ReclamationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BackController extends AbstractController
{

    #[Route('/back', name: 'display_dashboard')]
    public function indexAdmin(): Response
    {
        return $this->render('back/index.html.twig'
        );
    }

}

