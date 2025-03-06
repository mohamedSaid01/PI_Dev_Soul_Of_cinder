<?php

// src/Service/ZeroBounceEmailValidator.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ZeroBounceEmailValidator
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, #[Autowire('%zerobounce_api_key%')] string $zeroBounceApiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $zeroBounceApiKey;
    }
    

    public function isValid(string $email): bool
    {
        $response = $this->httpClient->request('GET', 'https://api.zerobounce.net/v2/validate', [
            'query' => [
                'api_key' => $this->apiKey,
                'email' => $email,
            ],
        ]);
    
        // Convertir la réponse en tableau
        $data = $response->toArray(false); // Désactiver l'exception automatique
    
        // Vérifier si la clé "status" existe
        if (!isset($data['status'])) {
            throw new \RuntimeException('Réponse de ZeroBounce invalide : ' . json_encode($data));
        }
    
        return $data['status'] === 'valid';
    }
    
}