<?php

namespace App\Service;

use App\Service\OpenRouterClient;

class AiTaskAssistantService
{
    public function __construct(
        private OpenRouterClient $client
    ) {
    }

    /**
     * @param string $description
     * @return array{estimation: int, priority: string, advice: string}
     */
    public function analyzeTask(string $description): array
    {
        $prompt = $this->buildPrompt($description);

        $messages = [
            ['role' => 'user', 'content' => $prompt],
        ];

        $response = $this->client->chat($messages);

        if (isset($response['error'])) {
            $errorMsg = $response['error']['message'] ?? 'Unknown API error';
            throw new \RuntimeException('AI Service Error: ' . $errorMsg);
        }

        // OpenRouter return format: ['choices'][0]['message']['content']
        $content = $response['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            throw new \RuntimeException('No response from AI service. Check your API key and quota.');
        }

        // Clean JSON response (remove markdown if present)
        $cleanJson = (string) preg_replace('/^```json\s*|\s*```$/i', '', trim($content));
        $data = json_decode($cleanJson, true);

        if (!$data || !isset($data['estimation'], $data['priority'], $data['advice'])) {
            // Log fallback or error
            throw new \RuntimeException('Invalid JSON response from AI service: ' . $content);
        }

        return [
            'estimation' => (int) $data['estimation'],
            'priority' => (string) $data['priority'],
            'advice' => (string) $data['advice'],
        ];
    }

    private function buildPrompt(string $description): string
    {
        return "Analyse this task: \"{$description}\"
        
        Return ONLY a JSON with this structure:
        {
            \"estimation\": 60,
            \"priority\": \"MEDIUM\",
            \"advice\": \"Consider adding specific acceptance criteria for the login validation.\"
        }
        
        Note: estimation must be an integer (in minutes).";
    }
}
