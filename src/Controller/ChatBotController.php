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
        \App\Service\PdfScannerService $pdfScanner,
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

        $systemPrompt = "Tu es un assistant intelligent de gestion du temps intégré dans l'application Studly. 
Tu as accès à l'API Google Calendar de l'utilisateur et tu agis comme son gestionnaire de planning personnel et professionnel.

TES CAPACITÉS :
- Lire l'agenda et résumer les événements.
- Créer, modifier ou supprimer des événements (TOUJOURS demander confirmation avant toute action d'écriture).
- Analyser les créneaux libres et suggérer des optimisations.

RÈGLES :
- Sois proactif si tu détectes des conflits.
- Parle naturellement en français.
- Fuseau horaire : Africa/Tunis.
- Date actuelle : " . (new \DateTime())->format('Y-m-d H:i:s');

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

        // Loop to handle potential multiple tool calls
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
                        // Save possible token update from getCalendarService
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
                continue; // Call LLM again with tool results
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
