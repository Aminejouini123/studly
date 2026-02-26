<?php

namespace App\Controller;

use App\Service\OpenRouterClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChatBotController extends AbstractController
{
    #[Route('/api/chat', name: 'api_chat', methods: ['POST'])]
    public function chat(
        Request $request, 
        OpenRouterClient $client,
        \App\Service\PdfScannerService $pdfScanner
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];
        $userMessage = trim((string)($payload['message'] ?? ''));
        $context = $payload['context'] ?? null;
        $action = $payload['action'] ?? null;

        if ($userMessage === '' && $action !== 'summarize_file') {
            return $this->json(['error' => 'Empty message'], 400);
        }

        $systemPrompt = 'You are an educational assistant for a course platform. 
        You must only answer questions related to courses, lessons, exams, or learning topics available on this website. 
        If a question is unrelated to education or the platform content, politely refuse. 
        Keep answers brief, clear, and helpful.';

        if ($context) {
            $systemPrompt .= "\n\nCONTEXT INFORMATION for the current course:\n";
            $systemPrompt .= json_encode($context, JSON_PRETTY_PRINT);
            
            // If the user wants a summary and a file is available
            if (($action === 'summarize_file' || str_contains(strtolower($userMessage), 'résumer le fichier')) && !empty($context['course_file'])) {
                $fileContent = $pdfScanner->extractText($context['course_file']);
                if (!empty($fileContent)) {
                    // Truncate to avoid token limits (OpenRouter handles large context but better safe)
                    $truncatedContent = mb_substr($fileContent, 0, 10000);
                    $systemPrompt .= "\n\nDOCUMENT CONTENT (Extracted from course PDF):\n" . $truncatedContent;
                    $systemPrompt .= "\n\nINSTRUCTION: The user wants a summary of this document. Please provide a well-structured, bulleted summary focusing on key learning objectives.";
                    if ($userMessage === '') { $userMessage = "S'il vous plaît, résumez le document de ce cours."; }
                } else {
                    return $this->json(['answer' => "Je suis désolé, je n'ai pas pu lire le contenu du fichier PDF de ce cours. Vérifiez que le fichier est bien un document texte lisible."]);
                }
            } else {
                $systemPrompt .= "\n\nPlease use this context to provide highly relevant answers to the user.";
            }
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ];

        $data = $client->chat($messages);

        if (isset($data['error'])) {
            return $this->json(['answer' => 'Erreur de l\'API IA : ' . ($data['error']['message'] ?? 'Erreur inconnue')]);
        }

        $answer = $data['choices'][0]['message']['content'] ?? null;

        return $this->json([
            'answer' => $answer,
            'raw' => $data['usageMetadata'] ?? null,
        ]);
    }

    #[Route('/chat', name: 'app_chat', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('chat/index.html.twig');
    }
}
