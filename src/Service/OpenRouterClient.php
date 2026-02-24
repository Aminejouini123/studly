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
        private string $appTitle,
    ) {}

    /**
     * @param array<int, array{role:string, content:string}> $messages
     * @param array|null $tools
     */
    public function chat(array $messages, ?array $tools = null): array
    {
        $json = [
            'model' => $this->model,
            'messages' => $messages,
        ];

        if ($tools) {
            $json['tools'] = $tools;
        }

        $response = $this->http->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
                // Optional but recommended by OpenRouter:
                'HTTP-Referer' => $this->appUrl,
                'X-Title' => $this->appTitle,
            ],
            'json' => $json,
            'timeout' => 30,
        ]);

        return $response->toArray(false);
    }
}
