<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class OpenAiImageService
{
    private string $apiKey;
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, string $apiKey, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
        $this->logger = $logger;
    }

    public function generateImage(string $prompt): string
    {
        try {
            $url = 'https://api.openai.com/v1/images/generations';

            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'dall-e-3', // Utiliser DALL·E 3 pour meilleure qualité
                    'prompt' => $prompt,
                    'n' => 1, // Nombre d’images à générer
                    'size' => '1024x1024', // Taille de l’image
                ],
            ]);

            $data = $response->toArray();
            $this->logger->info('Réponse OpenAI : ' . json_encode($data));

            if (!isset($data['data'][0]['url'])) {
                throw new \Exception('Réponse inattendue de OpenAI : ' . json_encode($data));
            }

            return $data['data'][0]['url']; // Retourne l'URL de l'image générée
        } catch (\Exception $e) {
            $this->logger->error('Erreur OpenAI : ' . $e->getMessage());
            return 'Erreur OpenAI : ' . $e->getMessage();
        }
    }
}
