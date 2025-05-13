<?php

namespace App\Controller;

use App\Service\HuggingFaceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface; 
class ChatbotController extends AbstractController
{
    private $huggingFaceService;

    public function __construct(HuggingFaceService $huggingFaceService)
    {
        $this->huggingFaceService = $huggingFaceService;
    }

  

    #[Route('/chatbot/form', name: 'chatbot_form', methods: ['GET', 'POST'])]
    public function chatbotForm(Request $request, SessionInterface $session): Response
    {
        // Initialisation des variables
        $messages = []; // Historique des messages
        $question = null; 
        $response = null; 
    
        // Récupérez l'historique des messages de la session
        if ($session->has('messages')) {
            $messages = $session->get('messages');
        }
    
        // Vérifiez si la méthode est POST
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $question = $data['question'] ?? ''; // Récupérez la question
    
            if (!empty($question)) {
                // Ajoutez la question à l'historique
                $messages[] = ['type' => 'user', 'text' => $question];
    
                // Obtenez la réponse du chatbot
                $response = $this->huggingFaceService->askMedicalAI($question);
                // Ajoutez la réponse à l'historique
                $messages[] = ['type' => 'bot', 'text' => $response];
    
                // Stockez l'historique des messages dans la session
                $session->set('messages', $messages);
            }
    
            // Redirigez vers la même page pour éviter la soumission répétée
            return $this->redirectToRoute('chatbot_form');
        }
    
        // Effacez les données de la session si nécessaire
        // $session->remove('messages'); // Optionnel, si vous voulez réinitialiser
    
        return $this->render('chatbot.html.twig', [
            'messages' => $messages, // Passez l'historique des messages à la vue
        ]);
    }
    
    

    

}
