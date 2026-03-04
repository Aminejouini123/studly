<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;

class AiActivityGeneratorService
{
    public function __construct(
        private OpenRouterClient $client,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function generateActivityForCourse(
        Course $course,
        ?int $questionCount = null,
        string $quizType = 'multiple_choice',
        ?string $difficulty = null,
        ?string $activityName = null
    ): ?Activity {
        $resolvedDifficulty = $difficulty ?? $course->getDifficultyLevel();
        if (!$resolvedDifficulty) {
            $resolvedDifficulty = 'Medium';
        }
        $type = $this->resolveType($quizType, $resolvedDifficulty);

        // Use a different prompt strategy for quizzes vs other types
        if ($type === 'quiz') {
            return $this->generateQuizActivity($course, $questionCount ?? 5, $quizType, $resolvedDifficulty, $activityName);
        }

        return $this->generateStandardActivity($course, $type, $resolvedDifficulty, $activityName);
    }

    /**
     * Generate a quiz activity with properly structured JSON questions
     * that the frontend quiz player can parse and render.
     */
    private function generateQuizActivity(
        Course $course,
        int $questionCount,
        string $quizType,
        string $difficulty,
        ?string $activityName
    ): ?Activity {
        $titleHint = $activityName ? "Preferred title: \"{$activityName}\"." : '';

        $questionFormat = match ($quizType) {
            'true_false' => 'Each question must have exactly 2 options: ["True", "False"]. correct_answer_index is 0 for True, 1 for False.',
            'mixed' => 'Mix multiple-choice questions (4 options) and true/false questions (2 options: ["True", "False"]).',
            default => 'Each question must have exactly 4 options (A, B, C, D).',
        };

        $prompt = <<<PROMPT
You are an expert quiz generator for educational platforms.
{$titleHint}

Course: {$course->getName()}
Description: {$course->getComment()}
Level: {$course->getSemester()}
Difficulty: {$difficulty}

Generate EXACTLY {$questionCount} quiz questions about this course.

RULES:
- {$questionFormat}
- correct_answer_index must be the 0-based index of the correct option in the options array.
- explanation must explain WHY the correct answer is right (1-2 sentences).
- Questions must be varied, covering different aspects of the course.
- Questions must match the difficulty level: Easy = basic recall, Medium = understanding, Hard = analysis.

Respond ONLY with this exact JSON structure, no extra text:
{
    "title": "Quiz title here",
    "description": "Brief professional description of the quiz",
    "duration": {$questionCount},
    "questions": [
        {
            "question": "Question text here?",
            "options": ["Option A", "Option B", "Option C", "Option D"],
            "correct_answer_index": 0,
            "explanation": "Why this is the correct answer."
        }
    ],
    "hints": "General tips for the student taking this quiz",
    "expected_output": "Success criteria description"
}
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => 'You are an educational quiz generator. Respond ONLY with valid JSON. No markdown fences, no explanation outside JSON.'],
            ['role' => 'user', 'content' => $prompt],
        ];

        try {
            $response = $this->client->chat($messages);
        } catch (\Throwable $e) {
            return null;
        }

        $content = $response['choices'][0]['message']['content'] ?? null;
        if (!$content) {
            return null;
        }

        // Clean markdown fences
        $cleanJson = (string) preg_replace('/^```(?:json)?\s*/i', '', trim((string) $content));
        $cleanJson = (string) preg_replace('/\s*```$/i', '', $cleanJson);
        $data = json_decode($cleanJson, true);

        if (!$data || !isset($data['questions']) || !is_array($data['questions'])) {
            return null;
        }

        // Validate and sanitize each question
        $validQuestions = [];
        foreach ($data['questions'] as $q) {
            if (
                !isset($q['question'], $q['options'], $q['correct_answer_index']) ||
                !is_array($q['options']) || count($q['options']) < 2
            ) {
                continue;
            }
            $validQuestions[] = [
                'question' => (string) $q['question'],
                'options' => array_map('strval', $q['options']),
                'correct_answer_index' => (int) $q['correct_answer_index'],
                'explanation' => (string) ($q['explanation'] ?? ''),
            ];
        }

        if (empty($validQuestions)) {
            return null;
        }

        // Store the questions array as a JSON string in instructions — this is what the frontend quiz player expects
        $activity = new Activity();
        $activity->setTitle($activityName ?? $data['title'] ?? 'Quiz');
        $activity->setDescription($data['description'] ?? '');
        $activity->setDuration($data['duration'] ?? $questionCount * 2);
        $activity->setType('quiz');
        $jsonInstructions = json_encode($validQuestions, JSON_UNESCAPED_UNICODE);
        $activity->setInstructions($jsonInstructions !== false ? $jsonInstructions : '[]');
        $activity->setExpectedOutput($data['expected_output'] ?? 'Answer all questions correctly.');
        $activity->setHints($data['hints'] ?? '');
        $activity->setCourse($course);
        $activity->setLevel((string) ($course->getSemester() ?: 'Beginner'));
        $activity->setDifficulty(in_array($difficulty, ['Easy', 'Medium', 'Hard']) ? $difficulty : 'Medium');
        $activity->setStatus('to do');

        $this->entityManager->persist($activity);
        $this->entityManager->flush();

        return $activity;
    }

    /**
     * Generate a standard (challenge / mini_project) activity.
     */
    private function generateStandardActivity(
        Course $course,
        string $type,
        string $difficulty,
        ?string $activityName
    ): ?Activity {
        $titleHint = $activityName ? "Preferred title: \"{$activityName}\"." : '';

        $typeGuidelines = match ($type) {
            'challenge' => 'Provide a specific technical challenge with clear constraints and a list of required features.',
            'mini_project' => 'Include an architecture overview, a set of milestones, and detailed requirements.',
            default => 'Provide step-by-step instructions.',
        };

        $prompt = <<<PROMPT
Generate a highly professional learning activity for the following course.
{$titleHint}
Course Title: {$course->getName()}
Course Description: {$course->getComment()}
Level: {$course->getSemester()}
Difficulty: {$difficulty}

Requirements:
1. Type: {$type}
2. Content Guidelines: {$typeGuidelines}
3. Duration: realistic for the difficulty (in minutes, integer)
4. Instructions: comprehensive, structured with Markdown, pedagogically sound.
5. Expected Output: define exactly what success looks like.
6. Respond ONLY with this JSON structure — no extra text:

{
    "title": "string",
    "description": "Professional overview",
    "duration": 30,
    "instructions": "Full content here",
    "expected_output": "Detailed success criteria",
    "hints": "Helpful tips for students"
}
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => 'You are an educational AI content generator. Respond ONLY with valid JSON, no markdown fences, no explanation.'],
            ['role' => 'user', 'content' => $prompt],
        ];

        try {
            $response = $this->client->chat($messages);
        } catch (\Throwable $e) {
            return null;
        }

        $content = $response['choices'][0]['message']['content'] ?? null;
        if (!$content) {
            return null;
        }

        $cleanJson = (string) preg_replace('/^```(?:json)?\s*/i', '', trim((string) $content));
        $cleanJson = (string) preg_replace('/\s*```$/i', '', $cleanJson);
        $data = json_decode($cleanJson, true);

        if (!$data || !isset($data['title'])) {
            return null;
        }

        $activity = new Activity();
        $activity->setTitle($activityName ?? $data['title']);
        $activity->setDescription($data['description'] ?? '');
        $activity->setDuration($data['duration'] ?? 30);
        $activity->setType($type);
        $activity->setInstructions($data['instructions'] ?? '');
        $activity->setExpectedOutput($data['expected_output'] ?? '');
        $activity->setHints($data['hints'] ?? '');
        $activity->setCourse($course);
        $activity->setLevel((string) ($course->getSemester() ?: 'Beginner'));
        $activity->setDifficulty(in_array($difficulty, ['Easy', 'Medium', 'Hard']) ? $difficulty : 'Medium');
        $activity->setStatus('to do');

        $this->entityManager->persist($activity);
        $this->entityManager->flush();

        return $activity;
    }

    private function resolveType(string $quizType, string $difficulty): string
    {
        if (in_array($quizType, ['quiz', 'challenge', 'mini_project'])) {
            return $quizType;
        }

        // For MCQ/true_false/mixed quiz types, the activity type is 'quiz'
        if (in_array($quizType, ['multiple_choice', 'true_false', 'mixed'])) {
            return 'quiz';
        }

        if (stripos($difficulty, 'Easy') !== false) {
            return 'quiz';
        }
        if (stripos($difficulty, 'Hard') !== false) {
            return 'mini_project';
        }

        return 'challenge';
    }
}
