<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class DeepAiImageService
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
            $url = 'https://api.deepai.org/api/text2img';

            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'text' => $prompt,
                ],
            ]);

            $data = $response->toArray();
            $this->logger->info('Réponse DeepAI : ' . json_encode($data));

            if (!isset($data['output_url'])) {
                throw new \Exception('Réponse inattendue de DeepAI : ' . json_encode($data));
            }

            return $data['output_url']; // Retourne l'URL de l'image générée
        } catch (\Exception $e) {
            $this->logger->error('Erreur DeepAI : ' . $e->getMessage());
            return 'Erreur DeepAI : ' . $e->getMessage();
        }
    }
}
