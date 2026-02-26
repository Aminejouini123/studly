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
        private EntityManagerInterface $entityManager
    ) {}

    public function generateActivityForCourse(
        Course $course,
        int $questionCount = 10,
        string $quizType = 'multiple_choice',
        string $difficulty = 'Medium',
        ?string $activityName = null
    ): ?Activity
    {
        $prompt = $this->buildPrompt($course, $questionCount, $quizType, $difficulty, $activityName);

        $messages = [
            ['role' => 'system', 'content' => 'You are an educational AI content generator. You must respond ONLY with valid JSON.'],
            ['role' => 'user', 'content' => $prompt],
        ];

        $response = $this->client->chat($messages);
        
        if (isset($response['error'])) {
            return null;
        }

        // OpenRouter return format: ['choices'][0]['message']['content']
        $content = $response['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            return null;
        }

        // Clean JSON response (AI sometimes adds markdown fences)
        $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($content));
        $data = json_decode($cleanJson, true);

        if (!$data || !isset($data['title'])) {
            return null;
        }

        $activity = new Activity();
        $activity->setTitle($data['title']);
        $activity->setDescription($data['description'] ?? '');
        $activity->setDuration($data['duration'] ?? 30);
        $activity->setType($data['type'] ?? 'quiz');
        $instructions = isset($data['instructions']) 
            ? (is_array($data['instructions']) ? json_encode($data['instructions']) : $data['instructions']) 
            : '';
            
        $activity->setInstructions($instructions);
        $activity->setExpectedOutput($data['expected_output'] ?? '');
        $activity->setHints($data['hints'] ?? '');
        
        // Map Course fields
        $activity->setCourse($course);
        $activity->setLevel($course->getSemester() ?? 'General'); 
        $activity->setDifficulty($difficulty);
        $activity->setStatus('Active');

        $this->entityManager->persist($activity);
        $this->entityManager->flush();

        return $activity;
    }

    private function buildPrompt(
        Course $course, 
        int $questionCount, 
        string $quizType, 
        string $difficulty, 
        ?string $activityName
    ): string
    {
        $typeGuidelines = match($quizType) {
            'multiple_choice' => "Include exactly {$questionCount} multiple-choice questions with 4 options each and indicate the correct answer index (0-3).",
            'true_false' => "Include exactly {$questionCount} true or false questions. Options must be ['True', 'False'] and indicate correct index (0 or 1).",
            'mixed' => "Include exactly {$questionCount} questions, alternating between multiple-choice and true/false.",
            default => "Provide {$questionCount} questions for this activity."
        };

        $activityTitle = $activityName ?: "Quiz for " . $course->getName();

        return "Generate a highly professional learning activity for the following course:
        Course Title: {$course->getName()}
        Course Description: {$course->getComment()}
        Course Level: {$course->getSemester()}
        Requested Activity Name: {$activityTitle}
        Requested Difficulty: {$difficulty}

        Requirements:
        1. Type: {$quizType}
        2. Content Guidelines: {$typeGuidelines}
        3. Question Count: {$questionCount}
        4. Duration: Estimate a realistic duration (in minutes) for a student to complete this {$difficulty} level activity.
        5. Instructions: MUST be a JSON array of question objects.
        6. Expected Output: Briefly describe the learning objectives.
        7. Format: Final response must be valid JSON only.

        Response JSON Format:
        {
            \"title\": \"{$activityTitle}\",
            \"description\": \"Professional overview summarizing the activity's purpose\",
            \"duration\": 30,
            \"type\": \"quiz\",
            \"instructions\": [
                {
                    \"question\": \"Text of the question\",
                    \"options\": [\"Option 1\", \"Option 2\", \"Option 3\", \"Option 4\"],
                    \"correct_answer_index\": 0,
                    \"explanation\": \"Why this answer is correct\"
                }
            ],
            \"expected_output\": \"Detailed success criteria and learning outcomes\",
            \"hints\": \"Helpful tips for students to prepare for this quiz\"
        }";
    }
}
