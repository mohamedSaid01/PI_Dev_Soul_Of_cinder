<?php
// src/Controller/TestController.php
namespace App\Controller;

use App\Service\GeminiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'test_gemini')]
    public function test(GeminiService $geminiService): Response
    {
        // Génère une description
        $prompt = "Génère une description pour un événement médical intitulé 'Don du sang'.";
        $result = $geminiService->generateText($prompt);

        return new Response($result);
    }
}