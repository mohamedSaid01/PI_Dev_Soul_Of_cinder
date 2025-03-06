<?php
namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GeminiService
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function generateText(string $prompt): string
    {
        $client = new Client();
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->apiKey}";
        $promptEnFrancais = "Génère une description en français pour un événement médical intitulé : " . $prompt;

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $promptEnFrancais]
                    ]
                ]
            ]
        ];

        try {
            $response = $client->post($url, [
                'json' => $data,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);
            return $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Erreur lors de la génération de la description.';
        } catch (GuzzleException $e) {
            throw new \Exception('Erreur lors de la génération de la description : ' . $e->getMessage());
        }
    }
}