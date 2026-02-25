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
        \App\Service\FileScannerService $fileScanner,
        \App\Service\GoogleCalendarService $calendarService,
        \Doctrine\ORM\EntityManagerInterface $em
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];
        $userMessage = trim((string)($payload['message'] ?? ''));
        $context = $payload['context'] ?? null;
        $action = $payload['action'] ?? null;

        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        if ($userMessage === '' && $action !== 'summarize_file') {
            return $this->json(['error' => 'Empty message'], 400);
        }

        $systemPrompt = "You are an educational assistant for the 'Studly' platform. Always respond in French naturally.\n";

        if ($context) {
            $isExam = isset($context['exam_file']) || (isset($context['type']) && strtolower($context['type']) === 'exam');
            $isCourse = isset($context['course_file']) || (isset($context['semester']));
            $isActivity = isset($context['activity_file']);

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
                4. Focus on making the student a master of the subject.";
            } else {
                $systemPrompt .= "You are an educational assistant. Answer questions clearly and helpfully based on the context provided.";
            }

            $systemPrompt .= "\n\nCONTEXT INFORMATION:\n" . json_encode($context, JSON_PRETTY_PRINT);
            
            $fileName = $context['course_file'] ?? $context['exam_file'] ?? $context['activity_file'] ?? null;
            $subDir = $isExam ? 'exams' : ($isActivity ? 'activities' : 'courses');

            if (!empty($fileName)) {
                $fileContent = $fileScanner->extractText($fileName, $subDir);
                if (!empty($fileContent)) {
                    $truncatedContent = mb_substr($fileContent, 0, 12000); 
                    $systemPrompt .= "\n\nDOCUMENT CONTENT (Extracted from " . $subDir . "):\n" . $truncatedContent;
                    
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
            $systemPrompt .= "Tu as accès à l'API Google Calendar de l'utilisateur et tu agis comme son gestionnaire de planning personnel et professionnel.
TES CAPACITÉS :
- Lire l'agenda et résumer les événements.
- Créer, modifier ou supprimer des événements (TOUJOURS demander confirmation avant toute action d'écriture).
- Analyser les créneaux libres et suggérer des optimisations.

RÈGLES :
- Sois proactif si tu détectes des conflits.
- Parle naturellement en français.
- Fuseau horaire : Africa/Tunis.
- Date actuelle : " . (new \DateTime())->format('Y-m-d H:i:s');
        }

        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_events',
                    'description' => 'Récupère les événements du calendrier sur une période donnée',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'timeMin' => ['type' => 'string', 'description' => 'ISO 8601 datetime (default: now)'],
                            'timeMax' => ['type' => 'string', 'description' => 'ISO 8601 datetime'],
                            'maxResults' => ['type' => 'integer', 'default' => 10],
                            'q' => ['type' => 'string', 'description' => 'Recherche textuelle'],
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_event',
                    'description' => 'Crée un nouvel événement dans le calendrier',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'summary' => ['type' => 'string', 'description' => 'Titre'],
                            'description' => ['type' => 'string'],
                            'start' => ['type' => 'string', 'description' => 'ISO 8601 datetime'],
                            'end' => ['type' => 'string', 'description' => 'ISO 8601 datetime'],
                            'location' => ['type' => 'string'],
                            'attendees' => ['type' => 'array', 'items' => ['type' => 'string']],
                        ],
                        'required' => ['summary', 'start', 'end']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'update_event',
                    'description' => 'Modifie un événement existant',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'eventId' => ['type' => 'string'],
                            'updates' => [
                                'type' => 'object',
                                'properties' => [
                                    'summary' => ['type' => 'string'],
                                    'description' => ['type' => 'string'],
                                    'start' => ['type' => 'string'],
                                    'end' => ['type' => 'string'],
                                    'location' => ['type' => 'string'],
                                ]
                            ]
                        ],
                        'required' => ['eventId', 'updates']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'delete_event',
                    'description' => 'Supprime un événement',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'eventId' => ['type' => 'string'],
                        ],
                        'required' => ['eventId']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_free_slots',
                    'description' => 'Trouve les créneaux libres dans l\'agenda',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'timeMin' => ['type' => 'string', 'description' => 'ISO 8601 datetime'],
                            'timeMax' => ['type' => 'string', 'description' => 'ISO 8601 datetime'],
                            'durationMinutes' => ['type' => 'integer', 'default' => 60],
                        ],
                        'required' => ['timeMin', 'timeMax']
                    ]
                ]
            ]
        ];

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ];

        for ($i = 0; $i < 5; $i++) {
            $data = $client->chat($messages, $tools);
            $message = $data['choices'][0]['message'] ?? null;

            if (!$message) break;

            if (!empty($message['tool_calls'])) {
                $messages[] = $message;

                foreach ($message['tool_calls'] as $toolCall) {
                    $functionName = $toolCall['function']['name'];
                    $args = json_decode($toolCall['function']['arguments'], true);
                    $result = null;

                    try {
                        switch ($functionName) {
                            case 'list_events':
                                $result = $calendarService->listEvents($user, $args['timeMin'] ?? 'now', $args['timeMax'] ?? null, $args['maxResults'] ?? 10, $args['q'] ?? null);
                                break;
                            case 'create_event':
                                $result = $calendarService->createEvent($user, $args);
                                break;
                            case 'update_event':
                                $result = $calendarService->updateEvent($user, $args['eventId'], $args['updates']);
                                break;
                            case 'delete_event':
                                $result = $calendarService->deleteEvent($user, $args['eventId']);
                                break;
                            case 'get_free_slots':
                                $result = $calendarService->getFreeSlots($user, $args['timeMin'], $args['timeMax'], $args['durationMinutes'] ?? 60);
                                break;
                        }
                        $em->flush();
                    } catch (\Exception $e) {
                        $result = ['error' => $e->getMessage()];
                    }

                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'name' => $functionName,
                        'content' => json_encode($result),
                    ];
                }
                continue;
            }

            $answer = $message['content'];
            return $this->json([
                'answer' => $answer,
                'raw' => $data['usage'] ?? null,
            ]);
        }

        return $this->json(['error' => 'Too many steps or no response'], 500);
    }

    #[Route('/chat', name: 'app_chat', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('chat/index.html.twig');
    }
}
