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
        // Convert messages to Gemini's format
        $contents = [];
        foreach ($messages as $msg) {
            $role = ($msg['role'] === 'assistant') ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content']]]
            ];
        }

        $jsonBody = [
            'contents' => $contents,
        ];

        // Ensure model name is trimmed
        $model = trim($this->model);

        $response = $this->http->request('POST', "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$this->apiKey}", [
            'headers' => [
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
                    'message' => 'L\'API Gemini n\'a pas renvoyé un JSON valide. Contenu reçu : ' . mb_substr($content, 0, 500)
                ]
            ];
        }

        if (isset($data['error'])) {
            // To ensure the application remains executable during presentations even if the quota is exceeded,
            // we provide fallback mock responses based on the context of the request.
            $promptContent = '';
            foreach ($messages as $msg) {
                $promptContent .= strtolower($msg['content']);
            }

            $mockContent = '';

            if (str_contains($promptContent, 'estimation": 60')) {
                // Task assistant mock
                $mockContent = json_encode([
                    'estimation' => 45,
                    'priority' => 'MEDIUM',
                    'advice' => 'Mode hors-ligne (Quota API dépassé). Je vous conseille de découper cette tâche en petites étapes.'
                ]);
            } elseif (str_contains($promptContent, 'expected_output')) {
                // Activity generator mock
                $mockContent = json_encode([
                    'title' => 'Activité Générée par l\'IA (Mode Hors-Ligne)',
                    'description' => 'Cette activité a été générée en mode hors-ligne car le quota de l\'API a été dépassé.',
                    'duration' => 60,
                    'type' => 'challenge',
                    'instructions' => '1. Lisez le cours. 2. Faites les exercices.',
                    'expected_output' => 'Vous devez avoir compris les concepts clés.',
                    'hints' => 'N\'hésitez pas à poser des questions.'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                // Chatbot mock
                $errorMsg = $data['error']['message'] ?? 'Erreur inconnue';
                $mockContent = "L'assistant IA est actuellement indisponible (Quota API dépassé : {$errorMsg}). Ceci est une réponse automatique pour vous permettre de continuer à utiliser l'application.";
            }

            return [
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => $mockContent]
                            ]
                        ]
                    ]
                ]
            ];
        }

        // Return the raw Gemini structure or a mapped version for compatibility
        return $data;
    }
}
