<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;

class AiActivityGeneratorService
{
    public function __construct(
        private GeminiClient $client,
        private EntityManagerInterface $entityManager
    ) {}

    public function generateActivityForCourse(Course $course): ?Activity
    {
        $prompt = $this->buildPrompt($course);

        $messages = [
            ['role' => 'system', 'content' => 'You are an educational AI content generator. You must respond ONLY with valid JSON.'],
            ['role' => 'user', 'content' => $prompt],
        ];

        $response = $this->client->chat($messages);
        
        if (isset($response['error'])) {
            return null;
        }

        $content = $response['candidates'][0]['content']['parts'][0]['text'] ?? null;

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
        $activity->setType($data['type'] ?? 'challenge');
        $activity->setInstructions($data['instructions'] ?? '');
        $activity->setExpectedOutput($data['expected_output'] ?? '');
        $activity->setHints($data['hints'] ?? '');
        
        // Map Course fields
        $activity->setCourse($course);
        $activity->setLevel($course->getSemester() ?? 'General'); // Using semester as level if level is not explicit
        $activity->setDifficulty($course->getDifficultyLevel() ?? 'Medium');
        $activity->setStatus('Active');

        $this->entityManager->persist($activity);
        $this->entityManager->flush();

        return $activity;
    }

    private function buildPrompt(Course $course): string
    {
        $difficulty = $course->getDifficultyLevel();
        
        // Logic rules: Easy -> quiz, Medium -> challenge, Hard -> mini_project
        $suggestedType = 'challenge';
        if (stripos($difficulty, 'Easy') !== false) $suggestedType = 'quiz';
        if (stripos($difficulty, 'Hard') !== false) $suggestedType = 'mini_project';

        $typeGuidelines = match($suggestedType) {
            'quiz' => 'Include 5 multiple-choice questions with 4 options each. Format them clearly in the instructions.',
            'challenge' => 'Provide a specific technical challenge with clear constraints and a list of required features.',
            'mini_project' => 'Include an architecture overview, a set of milestones, and detailed requirements.',
            default => 'Provide step-by-step instructions.'
        };

        return "Generate a highly professional learning activity for the following course:
        Course Title: {$course->getName()}
        Course Description: {$course->getComment()}
        Level: {$course->getSemester()}
        Difficulty: {$difficulty}

        Requirements:
        1. Type: {$suggestedType}
        2. Content Guidelines: {$typeGuidelines}
        3. Duration: must be realistic for the difficulty (in minutes)
        4. Instructions: must be comprehensive, structured with Markdown, and pedagogically sound.
        5. Expected Output: define exactly what success looks like.
        6. Format: Final response must be valid JSON only.

        Response JSON Format:
        {
            \"title\": \"string\",
            \"description\": \"Professional overview\",
            \"duration\": 30,
            \"type\": \"{$suggestedType}\",
            \"instructions\": \"Full content here (questions for quiz, steps for challenge)\",
            \"expected_output\": \"Detailed success criteria\",
            \"hints\": \"Helpful tips for students\"
        }";
    }
}
