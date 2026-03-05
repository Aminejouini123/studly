<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OpenRouterClient
{
    public function __construct(
        private HttpClientInterface $http,
        private string $apiKey,
        private string $model,
        private string $appUrl,
        private string $appTitle
    ) {}

    /**
     * @param array<int, array{role:string, content:string}> $messages
     */
    public function chat(array $messages): array
    {
        $jsonBody = [
            'model' => $this->model,
            'messages' => $messages,
        ];

        $response = $this->http->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer' => $this->appUrl,
                'X-Title' => $this->appTitle,
                'Content-Type' => 'application/json',
            ],
            'json' => $jsonBody,
            'timeout' => 30,
        ]);

        $content = $response->getContent(false);
        $data = json_decode($content, true);

        if (!$data) {
            return [
                'error' => [
                    'message' => 'L\'API OpenRouter n\'a pas renvoyé un JSON valide. Contenu reçu : ' . mb_substr($content, 0, 500)
                ]
            ];
        }

        return $data;
    }
}
