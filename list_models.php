<?php
// list_models.php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$apiKey = $_ENV['GEMINI_API_KEY'] ?? 'MISSING_KEY';
echo "Checking API Key: " . substr($apiKey, 0, 8) . "...\n";

$client = HttpClient::create();

foreach (['v1', 'v1beta'] as $version) {
    echo "\nTesting version: $version\n";
    try {
        $response = $client->request('GET', "https://generativelanguage.googleapis.com/{$version}/models?key={$apiKey}");
        $data = $response->toArray(false);
        
        if (isset($data['error'])) {
            echo "Error in $version: " . ($data['error']['message'] ?? 'Unknown error') . "\n";
            continue;
        }
        
        echo "Available models in $version:\n";
        foreach ($data['models'] ?? [] as $model) {
            echo "- " . $model['name'] . " (" . implode(', ', $model['supportedGenerationMethods']) . ")\n";
        }
    } catch (\Exception $e) {
        echo "Exception in $version: " . $e->getMessage() . "\n";
    }
}
