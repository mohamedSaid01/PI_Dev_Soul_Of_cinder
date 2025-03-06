<?php
// src/Service/DalleService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class DalleService
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
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/images/generations', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'prompt' => $prompt,
                    'n' => 1,
                    'size' => '1024x1024',
                ],
            ]);

            $data = $response->toArray();

            // Log pour voir la réponse OpenAI
            $this->logger->info('Réponse DALL-E : ' . json_encode($data));

            if (!isset($data['data'][0]['url'])) {
                throw new \Exception('Réponse inattendue de OpenAI : ' . json_encode($data));
            }

            return $data['data'][0]['url'];
        } catch (\Exception $e) {
            $this->logger->error('Erreur DALL-E : ' . $e->getMessage());
            return 'Erreur DALL-E : ' . $e->getMessage();
        }
    }
}
