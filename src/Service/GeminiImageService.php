<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class GeminiImageService
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
            $url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent?key=" . $this->apiKey;

            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => "Crée une image pour : " . $prompt]
                            ]
                        ]
                    ]
                ],
            ]);

            $data = $response->toArray();
            $this->logger->info('Réponse Gemini : ' . json_encode($data));

            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Réponse inattendue de Gemini : ' . json_encode($data));
            }

            return $data['candidates'][0]['content']['parts'][0]['text']; // URL de l'image générée
        } catch (\Exception $e) {
            $this->logger->error('Erreur Gemini : ' . $e->getMessage());
            return 'Erreur Gemini : ' . $e->getMessage();
        }
    }
}
