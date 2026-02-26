<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;

class AiActivityGeneratorService
{
    public function __construct( // Constructeur pour injecter les dépendances nécessaires
        private OpenRouterClient $client, // Injection du client API pour communiquer avec l'IA
        private EntityManagerInterface $entityManager, // Injection de Doctrine pour sauvegarder en base de données
        private FileScannerService $fileScanner // Injection du service pour lire le contenu des fichiers de cours
    ) {} // Fin du constructeur

    public function generateActivityForCourse(Course $course, ?int $questionCount = null, ?string $quizType = 'multiple_choice', ?string $difficultyOverride = null, ?string $activityName = null): ?Activity // Fonction pour générer une activité (Quiz) pour un cours
    { // Début de la fonction
        // Enforce minimum question count in PHP
        if ($questionCount !== null) {
            $questionCount = max(1, $questionCount);
        }
        $courseContent = ""; // Initialisation de la variable pour le texte du cours
        if ($course->getCourseFile()) { // Vérification si le cours possède un fichier attaché (PDF, Word, etc.)
            $courseContent = $this->fileScanner->extractText($course->getCourseFile()); // Extraction du texte à partir du fichier
            // Truncate to avoid context window issues (approx 10,000 characters is safe for most models)
            if (strlen($courseContent) > 10000) { // Si le texte est trop long (plus de 10 000 caractères)
                $courseContent = substr($courseContent, 0, 10000) . "... [Content Truncated]"; // On coupe le texte pour ne pas saturer l'IA
            } // Fin de la condition de tronquage
        } // Fin de la condition de fichier

        $prompt = $this->buildPrompt($course, $courseContent, $questionCount, $quizType, $difficultyOverride); // Construction de la consigne (prompt) détaillée pour l'IA

        $messages = [ // Préparation de la structure de discussion pour l'API
            ['role' => 'system', 'content' => 'You are an educational AI content generator. You must respond ONLY with valid JSON.'], // Consigne système : l'IA doit être un prof et répondre en JSON
            ['role' => 'user', 'content' => $prompt], // Consigne utilisateur : les détails du cours et le texte extrait
        ]; // Fin du tableau de messages

        $response = $this->client->chat($messages); // Envoi au client OpenRouter et attente de la réponse
        $content = $response['choices'][0]['message']['content'] ?? null; // Récupération du contenu textuel de la réponse

        if (!$content) { // Si l'IA n'a rien renvoyé (échec de connexion ou erreur)
            return null; // Retourne "vide" pour signaler l'échec
        } // Fin de la sécurité anti-vide

        // Clean JSON response (AI sometimes adds markdown fences)
        $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($content)); // Nettoyage de la réponse IA (on enlève les balises ```json si présentes)
        $data = json_decode($cleanJson, true); // Décodage du texte JSON en tableau PHP exploitable

        if (!$data || !isset($data['title'])) { // Si le JSON est invalide ou s'il manque le titre
            return null; // Retourne "vide" pour signaler une réponse IA incorrecte
        } // Fin de la sécurité JSON

        $activity = new Activity(); // Création d'une nouvelle instance de l'entité Activité
        $activity->setTitle($activityName ?: $data['title']); // Attribution du titre personnalisé ou celui généré par l'IA
        $activity->setDescription($data['description'] ?? ''); // Attribution de la description générée
        $activity->setDuration($data['duration'] ?? 30); // Attribution de la durée (ou 30 min par défaut)
        $activity->setType($data['type'] ?? 'quiz'); // Attribution du type (toujours "quiz")
        
        $instructions = $data['instructions'] ?? ''; // Récupération des questions (format JSON)
        if (is_array($instructions)) { // Si les instructions sont un tableau (cas des questions de quiz)
            $instructions = json_encode($instructions); // On les transforme en texte JSON pour le stocker en base
        } // Fin de la conversion
        $activity->setInstructions($instructions); // Enregistrement des questions dans le champ instructions
        $activity->setExpectedOutput($data['expected_output'] ?? ''); // Attribution du résultat attendu
        $activity->setHints($data['hints'] ?? ''); // Attribution des conseils/astuces
        
        // Map Course fields
        $activity->setCourse($course); // Liaison de l'activité au cours correspondant
        $activity->setLevel($course->getSemester() ?? 'General'); // Utilisation du semestre comme niveau de l'activité
        $activity->setDifficulty($difficultyOverride ?? $course->getDifficultyLevel() ?? 'Medium'); // Utilisation de la difficulté personnalisée ou celle du cours
        $activity->setStatus('Active'); // Définition du statut sur "Actif" par défaut

        $this->entityManager->persist($activity); // Préparation de l'enregistrement dans Doctrine
        $this->entityManager->flush(); // Sauvegarde réelle dans la base de données SQL

        return $activity; // Retourne l'objet Activité complet
    } // Fin de la fonction principale

    private function buildPrompt(Course $course, string $courseContent = "", ?int $questionCount = null, ?string $quizType = 'multiple_choice', ?string $difficultyOverride = null): string // Fonction privée pour fabriquer la consigne IA
    { // Début de la fonction
        $difficulty = $difficultyOverride ?? $course->getDifficultyLevel(); // Récupération du niveau de difficulté (priorité au override)
        
        // All generated activities are now quizzes
        $suggestedType = 'quiz'; // Le type est fixé à "quiz"
        
        // Use provided question count or fallback to difficulty-based logic
        if ($questionCount === null) {
            $questionCount = 10; // Nombre de questions par défaut (Moyen)
            if (stripos($difficulty, 'Easy') !== false) { // Si le cours est marqué comme "Easy"
                $questionCount = 5; // On limite à 5 questions
            } elseif (stripos($difficulty, 'Hard') !== false) { // Si le cours est marqué comme "Hard"
                $questionCount = 15; // On monte à 15 questions pour plus de défi
            } // Fin de la logique de nombre de questions
        }

        $suggestedDuration = $questionCount * 2; // On calcule la durée idéale (2 minutes par question)

        $sourceContext = !empty($courseContent) ? "
        ### SOURCE CONTENT FROM FILE (IMPORTANT):
        Analyze this text and base your generation EXCLUSIVELY on it:
        {$courseContent}
        " : "No file content available. Please base the generation on the Course Title and Description provided below."; // Préparation du contenu source pour l'IA

        $contentConstraint = !empty($courseContent) 
            ? "strictly related to the PROVIDED source content text above. DO NOT use external knowledge if it contradicts the file." 
            : "strictly related to the course subject matter described in the title and description."; // Contraintes pour forcer l'IA à rester sur le sujet

        return "Generate a highly professional learning activity for the following course:
        Course Title: {$course->getName()}
        Course Description: {$course->getComment()}
        Level: {$course->getSemester()}
        Difficulty: {$difficulty}

        {$sourceContext}

        Requirements:
        1. Type: {$quizType}
        2. Content Quality: Must be pedagogically sound, challenging, and {$contentConstraint}
        3. Duration: must be realistic for the difficulty. Suggested: {$suggestedDuration} minutes.
        4. Instructions: This field MUST be a JSON array of {$questionCount} high-quality questions.
           
           {% if quizType == 'true_false' %}
           Schema for True/False: [{\"question\": \"string\", \"options\": [\"True\", \"False\"], \"correct_answer_index\": 0_or_1, \"explanation\": \"string\"}]
           {% elseif quizType == 'multiple_choice' %}
           Schema for Multiple Choice: [{\"question\": \"string\", \"options\": [\"a\", \"b\", \"c\", \"d\"], \"correct_answer_index\": 0_to_3, \"explanation\": \"string\"}]
           {% else %}
           Mixed Schema: Mix between Multiple Choice (4 options) and True/False (2 options).
           {% endif %}

        5. Expected Output: define exactly what success looks like.
        6. Format: Final response must be valid JSON only.

        Response JSON Format:
        {
            \"title\": \"Professional Catchy Title\",
            \"description\": \"Professional and motivating overview (2-3 sentences)\",
            \"duration\": {$suggestedDuration},
            \"type\": \"quiz\",
            \"instructions\": JSON_ARRAY_OF_QUESTIONS,
            \"expected_output\": \"Detailed success criteria\",
            \"hints\": \"Helpful tips for students\"
        }"; // Construction et retour du prompt final complet au format texte
    } // Fin de la fonction de construction de prompt
}
