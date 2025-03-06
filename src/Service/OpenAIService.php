<?php
// src/Service/OpenAIService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAIService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    /**
     * Génère une image à partir d'un prompt en utilisant l'API OpenAI DALL-E.
     */
    public function generateImage(string $prompt): string
    {
        $url = 'https://api.openai.com/v1/images/generations';
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        $body = [
            'prompt' => $prompt,
            'n' => 1, // Nombre d'images à générer
            'size' => '256x256', // Taille de l'image
        ];

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => $headers,
                'json' => $body,
            ]);

            $responseData = $response->toArray();
            return $responseData['data'][0]['url'] ?? 'Erreur lors de la génération de l\'image.';
        } catch (\Exception $e) {
            // Affichez le message d'erreur complet
            throw new \Exception('Erreur lors de la génération de l\'image : ' . $e->getMessage());
        }
    }
}