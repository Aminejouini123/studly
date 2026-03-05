<?php
require_once 'vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$apiKey = $_ENV['GEMINI_API_KEY'];
$model = $_ENV['GEMINI_MODEL'];

echo "Testing Model: $model\n";

$client = HttpClient::create();
$baseUrl = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey";

try {
    $response = $client->request('POST', $baseUrl, [
        'json' => [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => 'Hi']]
                ]
            ]
        ]
    ]);
    
    $data = $response->toArray();
    echo "Success!\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (isset($response)) {
        echo "Response Body: " . $response->getContent(false) . "\n";
    }
}
