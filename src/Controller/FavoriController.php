<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FavoriController extends AbstractController
{
    #[Route('/favorites', name: 'favorites')]
    public function favorites(): Response
    {
        // Ensure the user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Get the current user
        $user = $this->getUser();

        // Fetch the user's favorite products
        $favoris = $user->getFavoris();

        // Render the favorites page
        return $this->render('favori/favorites.html.twig', [
            'favoris' => $favoris,
        ]);
    }
}