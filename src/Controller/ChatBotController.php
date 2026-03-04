<?php

namespace App\Controller;

use App\Service\OpenRouterClient;
use App\Service\PdfScannerService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class ChatBotController extends AbstractController
{
    #[Route('/api/chat', name: 'api_chat', methods: ['POST'])]
    public function chat(
        Request $request,
        OpenRouterClient $client,
        PdfScannerService $pdfScanner,
        #[Autowire(service: 'cache.app')] CacheInterface $cache
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];
        $userMessage = trim((string) ($payload['message'] ?? ''));
        $context = is_array($payload['context'] ?? null) ? $payload['context'] : null;
        $action = (string) ($payload['action'] ?? '');

        if ($userMessage === '' && $action !== 'summarize_file') {
            return $this->json(['error' => 'Empty message'], 400);
        }

        $systemPrompt = "Tu es Studly Assistant, un tuteur IA intelligent et bienveillant pour la plateforme Studly.
Ta mission est d'aider l'étudiant à comprendre ses cours et réussir ses examens.
Sois concis, professionnel et utilise un ton encourageant.
Tu ne dois répondre qu'aux questions liées aux cours, leçons, examens ou sujets d'apprentissage disponibles sur ce site.
Si une question n'est pas liée à l'éducation ou au contenu de la plateforme, refuse poliment.
Garde les réponses brèves, claires et utiles.";

        if ($context) {
            $systemPrompt .= "\n\nCONTEXT INFORMATION for the current learning item:\n";
            $systemPrompt .= json_encode($this->compactContext($context), JSON_UNESCAPED_UNICODE);

            $summaryRequested = $action === 'summarize_file'
                || str_contains(mb_strtolower($userMessage), 'resume')
                || str_contains(mb_strtolower($userMessage), 'resumer')
                || str_contains(mb_strtolower($userMessage), 'résumé')
                || str_contains(mb_strtolower($userMessage), 'résumer')
                || str_contains(mb_strtolower($userMessage), 'explique');

            $fileName = $context['course_file'] ?? $context['exam_file'] ?? null;

            if ($summaryRequested && is_string($fileName) && $fileName !== '') {
                $cacheKey = 'pdf_text_' . md5($fileName);
                $fileContent = $cache->get($cacheKey, function (ItemInterface $item) use ($pdfScanner, $fileName): string {
                    $item->expiresAfter(3600); // 1h cache to avoid repeated PDF parsing
                    return $pdfScanner->extractText($fileName);
                });

                if ($fileContent !== '') {
                    // Keep prompt smaller for faster model latency.
                    $truncatedContent = mb_substr($fileContent, 0, 5000);
                    $systemPrompt .= "\n\nDOCUMENT CONTENT (Extracted from file):\n{$truncatedContent}";
                    $systemPrompt .= "\n\nINSTRUCTION: The user wants a summary of this document. Provide a concise and structured summary with key points.";
                    if ($userMessage === '') {
                        $userMessage = "Please summarize the current document.";
                    }
                } else {
                    return $this->json([
                        'answer' => "Je n'ai pas pu lire le contenu du fichier. Verifiez que le document est lisible."
                    ]);
                }
            } else {
                $systemPrompt .= "\n\nPlease use this context to provide highly relevant answers to the user.";
            }
        }

        try {
            $data = $client->chat([
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'error' => 'AI request failed: ' . $e->getMessage(),
            ], 502);
        }

        if (isset($data['error'])) {
            return $this->json([
                'error' => 'AI API error: ' . ($data['error']['message'] ?? 'Unknown error'),
            ], 502);
        }

        $answer = $data['choices'][0]['message']['content'] ?? null;
        if (!is_string($answer) || trim($answer) === '') {
            return $this->json([
                'error' => 'AI returned an empty answer',
            ], 502);
        }

        return $this->json([
            'answer' => $answer,
            'raw' => $data['usage'] ?? null,
        ]);
    }

    /**
     * Keep only useful context keys and cap string sizes to reduce prompt/token size.
     *
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function compactContext(array $context): array
    {
        $allowedKeys = [
            'course_name',
            'course_type',
            'difficulty',
            'description',
            'semester',
            'teacher',
            'exam_title',
            'exam_difficulty',
            'exam_duration',
            'exam_date',
        ];

        $result = [];
        foreach ($allowedKeys as $key) {
            if (!array_key_exists($key, $context)) {
                continue;
            }

            $value = $context[$key];
            if (is_string($value)) {
                $result[$key] = mb_substr($value, 0, 300);
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    #[Route('/chat', name: 'app_chat', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('chat/index.html.twig');
    }
}
