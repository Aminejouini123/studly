<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Course;
use App\Service\AiActivityGeneratorService;
use App\Service\OpenRouterClient;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class AiActivityGeneratorServiceTest extends TestCase
{
    public function testGenerateActivityForCourseCreatesQuizActivity(): void
    {
        $course = (new Course())
            ->setName('Algorithms')
            ->setComment('Sorting and graph theory')
            ->setSemester('Intermediate')
            ->setDifficultyLevel('Medium');

        $client = $this->createClientReturning([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'title' => 'Algorithm Quiz',
                        'description' => 'Quiz description',
                        'duration' => 12,
                        'questions' => [[
                            'question' => 'What is BFS?',
                            'options' => ['Tree', 'Graph traversal', 'Sort', 'Hash'],
                            'correct_answer_index' => 1,
                            'explanation' => 'BFS traverses graph level by level.',
                        ]],
                        'hints' => 'Read carefully',
                        'expected_output' => 'Correctly answer questions',
                    ], JSON_UNESCAPED_UNICODE),
                ],
            ]],
        ]);

        $persisted = null;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($activity) use (&$persisted): bool {
                $persisted = $activity;
                return true;
            }));
        $entityManager->expects($this->once())->method('flush');

        $service = new AiActivityGeneratorService($client, $entityManager);
        $result = $service->generateActivityForCourse($course, 5, 'multiple_choice', 'Easy', 'Quiz custom');

        $this->assertNotNull($result);
        $this->assertSame($persisted, $result);
        $this->assertSame('quiz', $result->getType());
        $this->assertSame('Quiz custom', $result->getTitle());
        $this->assertSame('Easy', $result->getDifficulty());
        $this->assertSame('Intermediate', $result->getLevel());
        $this->assertSame($course, $result->getCourse());

        $questions = json_decode((string) $result->getInstructions(), true);
        $this->assertIsArray($questions);
        $this->assertSame('What is BFS?', $questions[0]['question'] ?? null);
    }

    public function testGenerateActivityForCourseCreatesChallengeActivity(): void
    {
        $course = (new Course())
            ->setName('Databases')
            ->setComment('Transactions and indexing')
            ->setSemester('Beginner')
            ->setDifficultyLevel('Medium');

        $client = $this->createClientReturning([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'title' => 'DB Challenge',
                        'description' => 'Build schema and queries',
                        'duration' => 30,
                        'instructions' => 'Implement SQL schema.',
                        'expected_output' => 'Working schema',
                        'hints' => 'Start with normalization',
                    ], JSON_UNESCAPED_UNICODE),
                ],
            ]],
        ]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');

        $service = new AiActivityGeneratorService($client, $entityManager);
        $result = $service->generateActivityForCourse($course, null, 'challenge', 'Medium', null);

        $this->assertNotNull($result);
        $this->assertSame('challenge', $result->getType());
        $this->assertSame('DB Challenge', $result->getTitle());
        $this->assertSame('Medium', $result->getDifficulty());
        $this->assertSame('Beginner', $result->getLevel());
    }

    public function testGenerateActivityForCourseReturnsNullWhenClientThrows(): void
    {
        $course = (new Course())
            ->setName('Networks')
            ->setComment('OSI layers')
            ->setSemester('Beginner');

        $client = $this->createClientThrowing(new \RuntimeException('API down'));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');

        $service = new AiActivityGeneratorService($client, $entityManager);
        $result = $service->generateActivityForCourse($course, 3, 'multiple_choice', 'Easy', null);

        $this->assertNull($result);
    }

    private function createClientReturning(array $payload): OpenRouterClient
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('toArray')
            ->with(false)
            ->willReturn($payload);

        $http = $this->createMock(HttpClientInterface::class);
        $http->expects($this->once())
            ->method('request')
            ->with('POST', 'https://openrouter.ai/api/v1/chat/completions', $this->isType('array'))
            ->willReturn($response);

        return new OpenRouterClient($http, 'key', 'model', 'http://localhost', 'Test');
    }

    private function createClientThrowing(\Throwable $exception): OpenRouterClient
    {
        $http = $this->createMock(HttpClientInterface::class);
        $http->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        return new OpenRouterClient($http, 'key', 'model', 'http://localhost', 'Test');
    }
}
