<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GeminiClient
{
    public function __construct(
        private HttpClientInterface $http,
        private string $apiKey,
        private string $model = 'gemini-1.5-flash'
    ) {}

    /**
     * @param array<int, array{role:string, content:string}> $messages
     */
    public function chat(array $messages): array
    {
        $baseUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $contents = [];
        foreach ($messages as $msg) {
            $role = ($msg['role'] === 'user') ? 'user' : 'model';
            // System instructions should ideally be handled differently in Gemini (API v1beta has system_instruction),
            // but for a simple chat implementation, we can prepend it or map it.
            // If it's a 'system' role, we'll map it to 'user' for simplicity or skip if not handled by standard prompt.
            if ($msg['role'] === 'system') {
                $contents[] = [
                    'role' => 'user',
                    'parts' => [['text' => "System instruction: " . $msg['content']]]
                ];
            } else {
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $msg['content']]]
                ];
            }
        }

        $response = $this->http->request('POST', $baseUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'contents' => $contents,
            ],
        ]);

        $data = $response->toArray(false);

        if (isset($data['error'])) {
            return [
                'error' => [
                    'message' => $data['error']['message'] ?? 'Unknown Gemini API error'
                ]
            ];
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) {
            return [
                'error' => [
                    'message' => 'No content returned from Gemini.'
                ]
            ];
        }

        // Map back to an OpenAI-like structure to minimize changes in other services
        return [
            'choices' => [
                [
                    'message' => [
                        'content' => $text,
                        'role' => 'assistant'
                    ]
                ]
            ],
            'usage' => $data['usageMetadata'] ?? null
        ];
    }
}
