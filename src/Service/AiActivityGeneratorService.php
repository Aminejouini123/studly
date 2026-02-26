<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Course;
use App\Service\OpenRouterClient;
use Doctrine\ORM\EntityManagerInterface;

class AiActivityGeneratorService
{
    public function __construct(
        private OpenRouterClient $client,
        private EntityManagerInterface $entityManager,
        private FileScannerService $fileScanner
    ) {}

    public function generateActivityForCourse(Course $course, ?int $questionCount = null, ?string $quizType = 'multiple_choice', ?string $difficultyOverride = null, ?string $activityName = null): ?Activity
    {
        if ($questionCount !== null) {
            $questionCount = max(1, $questionCount);
        }

        $courseContent = "";
        if ($course->getCourseFile()) {
            $courseContent = $this->fileScanner->extractText($course->getCourseFile());
            if (strlen($courseContent) > 10000) {
                $courseContent = substr($courseContent, 0, 10000) . "... [Content Truncated]";
            }
        }

        $prompt = $this->buildPrompt($course, $courseContent, $questionCount, $quizType, $difficultyOverride, $activityName);

        $messages = [
            ['role' => 'system', 'content' => 'You are an educational AI content generator. You must respond ONLY with valid JSON.'],
            ['role' => 'user', 'content' => $prompt],
        ];

        $response = $this->client->chat($messages);
        
        if (isset($response['error'])) {
            return null;
        }

        $content = $response['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            return null;
        }

        $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($content));
        $data = json_decode($cleanJson, true);

        if (!$data || !isset($data['title'])) {
            return null;
        }

        $activity = new Activity();
        $activity->setTitle($activityName ?: $data['title']);
        $activity->setDescription($data['description'] ?? '');
        $activity->setDuration($data['duration'] ?? 30);
        $activity->setType($data['type'] ?? 'quiz');
        
        $instructions = $data['instructions'] ?? '';
        if (is_array($instructions)) {
            $instructions = json_encode($instructions);
        }
        $activity->setInstructions($instructions);
        $activity->setExpectedOutput($data['expected_output'] ?? '');
        $activity->setHints($data['hints'] ?? '');
        
        $activity->setCourse($course);
        $activity->setLevel($course->getSemester() ?? 'General');
        $activity->setDifficulty($difficultyOverride ?? $course->getDifficultyLevel() ?? 'Medium');
        $activity->setStatus('Active');

        $this->entityManager->persist($activity);
        $this->entityManager->flush();

        return $activity;
    }

    private function buildPrompt(Course $course, string $courseContent = "", ?int $questionCount = null, ?string $quizType = 'multiple_choice', ?string $difficultyOverride = null, ?string $activityName = null): string
    {
        $difficulty = $difficultyOverride ?? $course->getDifficultyLevel() ?? 'Medium';
        
        if ($questionCount === null) {
            $questionCount = 10;
            if (stripos($difficulty, 'Easy') !== false) {
                $questionCount = 5;
            } elseif (stripos($difficulty, 'Hard') !== false) {
                $questionCount = 15;
            }
        }

        $suggestedDuration = $questionCount * 2;

        $sourceContext = !empty($courseContent) ? "
        ### SOURCE CONTENT FROM FILE (IMPORTANT):
        Analyze this text and base your generation EXCLUSIVELY on it:
        {$courseContent}
        " : "No file content available. Please base the generation on the Course Title and Description provided below.";

        $contentConstraint = !empty($courseContent) 
            ? "strictly related to the PROVIDED source content text above. DO NOT use external knowledge if it contradicts the file." 
            : "strictly related to the course subject matter described in the title and description.";

        $activityTitle = $activityName ?: "Quiz for " . $course->getName();

        $typeGuidelines = match($quizType) {
            'multiple_choice' => "Include exactly {$questionCount} multiple-choice questions with 4 options each and indicate the correct answer index (0-3).",
            'true_false' => "Include exactly {$questionCount} true or false questions. Options must be ['True', 'False'] and indicate correct index (0 or 1).",
            'mixed' => "Include exactly {$questionCount} questions, alternating between multiple-choice and true/false.",
            default => "Provide {$questionCount} questions for this activity."
        };

        return "Generate a highly professional learning activity for the following course:
        Course Title: " . $course->getName() . "
        Course Description: " . $course->getComment() . "
        Course Level: " . $course->getSemester() . "
        Requested Activity Name: " . $activityTitle . "
        Requested Difficulty: " . $difficulty . "

        {$sourceContext}

        Requirements:
        1. Type: {$quizType}
        2. Content Quality: Must be pedagogically sound, challenging, and {$contentConstraint}
        3. Content Guidelines: {$typeGuidelines}
        4. Question Count: {$questionCount}
        5. Duration: must be realistic for the difficulty. Suggested: {$suggestedDuration} minutes.
        6. Instructions: This field MUST be a JSON array of {$questionCount} high-quality questions.
           
           Schema for Questions: [{\"question\": \"string\", \"options\": [\"a\", \"b\", \"c\", \"d\"], \"correct_answer_index\": 0_to_3, \"explanation\": \"string\"}]

        7. Expected Output: define exactly what success looks like and learning objectives.
        8. Format: Final response must be valid JSON only.

        Response JSON Format:
        {
            \"title\": \"" . $activityTitle . "\",
            \"description\": \"Professional and motivating overview (2-3 sentences)\",
            \"duration\": {$suggestedDuration},
            \"type\": \"quiz\",
            \"instructions\": JSON_ARRAY_OF_QUESTIONS,
            \"expected_output\": \"Detailed success criteria and learning outcomes\",
            \"hints\": \"Helpful tips for students to prepare for this quiz\"
        }";
    }

}
