<?php

// src/Service/ZeroBounceEmailValidator.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ZeroBounceEmailValidator
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $zeroBounceApiKey)
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

        $data = $response->toArray();
        return $data['status'] === 'valid';
    }
}