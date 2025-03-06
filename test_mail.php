<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\AIPlatform\V1\Client\PredictionServiceClient;
use Google\Cloud\AIPlatform\V1\PredictRequest;
use Google\Protobuf\Value;
use Google\Protobuf\ListValue;

// Spécifiez le chemin du fichier JSON
putenv('GOOGLE_APPLICATION_CREDENTIALS=C:\Users\RayenSabri\Desktop\Projects\PI_Dev_Soul_Of_cinder-Said_Boubaker\config\google\credentials.json');

// Remplacez ces valeurs par vos clés API et ID de projet
$projectId = 'oceanic-abacus-422500-d7';
$modelId = 'gemini-pro';

// Créez une instance du client Gemini
$client = new PredictionServiceClient();

// Créez une requête de prédiction
$request = new PredictRequest();
$request->setEndpoint("projects/{$projectId}/locations/us-central1/models/{$modelId}");

// Créez une instance de Google\Protobuf\Value pour le prompt
$promptValue = new Value();
$promptValue->setStringValue('Génère une description pour un événement médical intitulé "Don du sang".');

// Créez une instance de Google\Protobuf\ListValue pour les instances
$instancesList = new ListValue();
$instancesList->getValues()[] = $promptValue;

// Définissez les instances dans la requête
$request->setInstances([$instancesList]);

try {
    // Envoyez la requête à l'API
    $response = $client->predict($request);
    $predictions = $response->getPredictions();

    // Affichez la réponse
    echo "Réponse de l'API Gemini :\n";
    print_r($predictions);
} catch (\Exception $e) {
    // En cas d'erreur, affichez le message d'erreur
    echo "Erreur lors de l'appel à l'API Gemini :\n";
    echo $e->getMessage();
}