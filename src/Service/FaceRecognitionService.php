<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class FaceRecognitionService
{
    private HttpClientInterface $client;
    private string $faceApiUrl;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $client, string $faceApiUrl, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->faceApiUrl = rtrim($faceApiUrl, '/');
        $this->logger = $logger;
    }

    public function register(int $userId, array $descriptor): array
    {
        try {
            $response = $this->client->request('POST', $this->faceApiUrl . '/face/register', [
                'json' => [
                    'user_id' => $userId,
                    'descriptor' => $descriptor
                ]
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Error registering face: ' . $e->getMessage());
            throw new \RuntimeException('Unable to register face descriptor.');
        }
    }

    public function login(array $descriptor): ?array
    {
        try {
            $response = $this->client->request('POST', $this->faceApiUrl . '/face/login', [
                'json' => [
                    'descriptor' => $descriptor
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                return $response->toArray();
            }

            return null;
        } catch (\Exception $e) {
            $this->logger->error('Error during face login: ' . $e->getMessage());
            return null;
        }
    }
}
