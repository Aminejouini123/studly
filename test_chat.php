<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;
use App\Service\GeminiClient;
use Symfony\Component\HttpClient\HttpClient;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$apiKey = $_ENV['GEMINI_API_KEY'];
$model = $_ENV['GEMINI_MODEL'];

echo "Testing Model: $model\n";

$http = HttpClient::create();
$client = new GeminiClient($http, $apiKey, $model);

$messages = [
    ['role' => 'user', 'content' => 'Hello, respond with "OK" if you can hear me.']
];

$result = $client->chat($messages);

echo "Response:\n";
echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
