<?php

namespace App\Controller;

use App\Service\OpenRouterClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChatBotController extends AbstractController // Définition de la classe du contrôleur qui hérite des outils de Symfony
{ // Début de la classe
    #[Route('/api/chat', name: 'api_chat', methods: ['POST'])] // Définition de la route API pour le chat (méthode POST)
    public function chat( // Fonction principale pour gérer les messages du chat
        Request $request, // Injection de la requête HTTP
        OpenRouterClient $client, // Injection du client API OpenRouter
        \App\Service\FileScannerService $fileScanner // Injection du service pour lire les fichiers
    ): JsonResponse { // La fonction retourne une réponse au format JSON
        $payload = json_decode($request->getContent(), true) ?? []; // Récupération et décodage des données JSON envoyées
        $userMessage = trim((string)($payload['message'] ?? '')); // Nettoyage et récupération du message de l'utilisateur
        $context = $payload['context'] ?? null; // Récupération du contexte (infos sur le cours actuel)
        $action = $payload['action'] ?? null; // Récupération de l'action spécifique (ex: résumé)

        if ($userMessage === '' && $action !== 'summarize_file') { // Vérification si le message est vide (sauf si c'est un résumé)
            return $this->json(['error' => 'Empty message'], 400); // Retourne une erreur si rien n'est envoyé
        } // Fin de la condition de vérification

        // Initialisation du prompt système
        $systemPrompt = "You are an educational assistant for the 'Studly' platform.\n";

        if ($context) {
            // Détection du type de contexte
            $isExam = isset($context['exam_file']) || (isset($context['type']) && strtolower($context['type']) === 'exam');
            $isCourse = isset($context['course_file']) || (isset($context['semester']));
            $isActivity = isset($context['activity_file']);

            // Définition des instructions spécifiques selon le contexte
            if ($isExam) {
                $systemPrompt .= "You are currently helping a student with an EXAM. 
                Your goals are:
                1. EXPLAIN the exam content and specific exercises.
                2. HELP the student solve exercises if they ask.
                3. CORRECT their answers if they provide them.
                4. Give tips to succeed in this specific exam.
                You HAVE full permission to discuss, solve, and analyze the exercises in the provided document.";
            } elseif ($isCourse) {
                $systemPrompt .= "You are currently helping a student with a COURSE. 
                Your goals are:
                1. Provide HIGH-QUALITY, PROFESSIONAL summaries.
                2. Use a sophisticated yet pedagogical academic tone.
                3. ASK insightful testing questions to the student to check their deep understanding.
                4. Explain complex concepts with clarity and real-world relevance.
                Focus on making the student a master of the subject.";
            } else {
                $systemPrompt .= "You are an educational assistant. Answer questions clearly and helpfully based on the context provided.";
            }

            $systemPrompt .= "\n\nCONTEXT INFORMATION:\n" . json_encode($context, JSON_PRETTY_PRINT);
            
            // Identification du fichier
            $fileName = $context['course_file'] ?? $context['exam_file'] ?? $context['activity_file'] ?? null;
            $subDir = $isExam ? 'exams' : ($isActivity ? 'activities' : 'courses');

            if (!empty($fileName)) {
                $fileContent = $fileScanner->extractText($fileName, $subDir);
                if (!empty($fileContent)) {
                    $truncatedContent = mb_substr($fileContent, 0, 12000); 
                    $systemPrompt .= "\n\nDOCUMENT CONTENT (Extracted from " . $subDir . "):\n" . $truncatedContent;
                    
                    // Si l'utilisateur demande explicitement un résumé ou si on est en mode "Action: Summarize"
                    if ($action === 'summarize_file' || preg_match('/résumer|récapitululer|summary|summarize/i', $userMessage)) {
                        $systemPrompt .= "\n\nINSTRUCTION FOR SUMMARY:
                        Please provide a MASTER-LEVEL PROFESSIONAL SUMMARY using this structure:
                        - **Executive Overview**: A high-level introduction to the core subject.
                        - **Core Learning Pillars**: Break down the most critical concepts into deep, well-explained points.
                        - **Practical Applications**: How this knowledge is applied in professional or real-world scenarios.
                        - **Critical Takeaways**: Key points the student MUST remember.
                        Use a very polished, professional, and expressive French.";
                        
                        if ($userMessage === '') $userMessage = "Résumez ce cours de manière professionnelle et détaillée s'il vous plaît.";
                    }
                }
            }
        } else {
            // Prompt par défaut sans contexte
            $systemPrompt .= "Answer questions related to education, courses, or exams. If a question is unrelated to the platform or learning, politely refuse.";
        }
        
        $systemPrompt .= "\nAlways respond in the same language as the user (usually French). Keep answers clear and professional.";

        $messages = [ // Préparation du tableau de messages pour l'API
            ['role' => 'system', 'content' => $systemPrompt], // Le rôle système (les instructions)
            ['role' => 'user', 'content' => $userMessage], // Le rôle utilisateur (la question)
        ]; // Fin du tableau

        $data = $client->chat($messages); // Envoi de la requête à l'IA via le service OpenRouterClient
        $answer = $data['choices'][0]['message']['content'] ?? null; // Récupération de la réponse textuelle de l'IA

        return $this->json([ // Retour de la réponse en JSON au frontend
            'answer' => $answer, // Le texte de la réponse
            'raw' => $data['usage'] ?? null, // Statistiques d'utilisation (options)
        ]); // Fin du retour JSON
    } // Fin de la fonction chat

    #[Route('/chat', name: 'app_chat', methods: ['GET'])] // Route pour afficher l'interface graphique du chat
    public function index(): Response // Fonction pour charger la page Twig
    { // Début de la fonction
        return $this->render('chat/index.html.twig'); // Rendu du template Twig
    } // Fin de la fonction
}
