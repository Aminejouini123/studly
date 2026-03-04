<?php

namespace App\Controller;

use App\Service\OpenRouterClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ChatBotController extends AbstractController
{
    #[Route('/api/chat', name: 'api_chat', methods: ['POST'])]
    public function chat(Request $request, OpenRouterClient $chatClient): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userMessage = $data['message'] ?? '';
        $context = $data['context'] ?? null;
        $action = $data['action'] ?? null;

        if (!$userMessage && !$action) {
            return $this->json(['error' => 'No message provided'], 400);
        }

        // Build contextual context for Gemini
        $contextStr = "";
        if ($context) {
            if (isset($context['course_name'])) {
                $contextStr .= "L'utilisateur consulte le cours: " . $context['course_name'] . ". ";
            }
            if (isset($context['exam_title'])) {
                $contextStr .= "L'utilisateur prépare l'examen: " . $context['exam_title'] . ". ";
            }
        }

        $prompt = "Tu es Studly Assistant, un tuteur IA intelligent et bienveillant pour la plateforme Studly. 
        Ta mission est d'aider l'étudiant à comprendre ses cours et réussir ses examens. 
        Sois concis, professionnel et utilise un ton encourageant.
        
        " . $contextStr . "
        
        Question de l'étudiant: " . ($userMessage ?: "Peux-tu me faire un résumé ?");

        $messages = [
            ['role' => 'user', 'content' => $prompt],
        ];

        try {
            $result = $chatClient->chat($messages);

            if (isset($result['error'])) {
                return $this->json(['error' => $result['error']['message'] ?? 'Unknown API error'], 500);
            }

            $answer = $result['choices'][0]['message']['content'] ?? "Désolé, je n'ai pas pu générer de réponse.";

            return $this->json(['answer' => $answer]);
        } catch (\Exception $e) {
            return $this->json(['error' => "Erreur de connexion à l'IA : " . $e->getMessage()], 500);
        }
    }
}
