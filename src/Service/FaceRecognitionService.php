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

    /**
     * @param array<int, float> $descriptor
     * @return array<string, mixed>
     */
    public function register(int $userId, array $descriptor): array
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('L\'ID de l\'utilisateur doit être positif');
        }

        if (empty($descriptor)) {
            throw new \InvalidArgumentException('Le descripteur facial est obligatoire');
        }

        try {
            $response = $this->client->request('POST', $this->faceApiUrl . '/face/register', [
                'json' => [
                    'user_id' => $userId,
                    'descriptor' => $descriptor
                ]
            ]);

            /** @var array<string, mixed> $data */
            $data = $response->toArray();
            return $data;
        } catch (\Exception $e) {
            $this->logger->error('Error registering face: ' . $e->getMessage());
            throw new \RuntimeException('Unable to register face descriptor.');
        }
    }

    /**
     * @param array<int, float> $descriptor
     * @return array<string, mixed>|null
     */
    public function login(array $descriptor): ?array
    {
        if (empty($descriptor)) {
            throw new \InvalidArgumentException('Le descripteur facial est obligatoire');
        }

        try {
            $response = $this->client->request('POST', $this->faceApiUrl . '/face/login', [
                'json' => [
                    'descriptor' => $descriptor
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                /** @var array<string, mixed> $data */
                $data = $response->toArray();
                return $data;
            }

            return null;
        } catch (\Exception $e) {
            $this->logger->error('Error during face login: ' . $e->getMessage());
            return null;
        }
    }
}
