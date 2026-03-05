<?php
require_once 'vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$apiKey = $_ENV['GEMINI_API_KEY'];
$client = HttpClient::create();

foreach (['v1', 'v1beta'] as $version) {
    echo "--- Version: $version ---\n";
    try {
        $response = $client->request('GET', "https://generativelanguage.googleapis.com/$version/models?key=$apiKey");
        $data = $response->toArray();
        if (isset($data['models'])) {
            foreach ($data['models'] as $model) {
                if (in_array('generateContent', $model['supportedGenerationMethods'])) {
                    echo "- " . $model['name'] . "\n";
                }
            }
        } else {
            echo "No models found or error: " . json_encode($data) . "\n";
        }
    } catch (\Exception $e) {
        echo "Error in $version: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
