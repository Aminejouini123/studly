<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class RecommendationService
{
    private HttpClientInterface $client;
    private string $recommendationApiUrl;

    public function __construct(HttpClientInterface $client, string $recommendationApiUrl)
    {
        $this->client = $client;
        $this->recommendationApiUrl = rtrim($recommendationApiUrl, '/');
    }

    /**
     * @return array{
     *     jobs: array<int, array{
     *         title: string,
     *         company_or_platform?: string,
     *         compatibility_score: int|float,
     *         strengths_match?: array<int, string>,
     *         skills_gap?: array<int, string>,
     *         personalized_summary: string,
     *         link?: string
     *     }>,
     *     courses: array<int, array{
     *         title: string,
     *         company_or_platform?: string,
     *         compatibility_score: int|float,
     *         strengths_match?: array<int, string>,
     *         personalized_summary: string,
     *         link?: string
     *     }>,
     *     roadmap?: array<int, array{
     *         step_number: int,
     *         type: string,
     *         title: string,
     *         duration_weeks: int,
     *         description: string
     *     }>,
     *     general_summary?: string
     * }
     */
    public function getRecommendationsForUser(User $user, ?string $targetJob = null): array
    {
        $profileData = [
            'skills' => $user->getSkills() ?? [],
            'educationLevel' => $user->getEducationLevel(),
            'jobTitle' => $user->getJobTitle(),
            'targetJob' => $targetJob,
        ];

        try {
            $response = $this->client->request('POST', $this->recommendationApiUrl . '/recommend', [
                'json' => [
                    'profile' => $profileData
                ],
                'timeout' => 30 // AI can be slow
            ]);

            /** @var array{
             *     jobs: array<int, array{
             *         title: string,
             *         company_or_platform?: string,
             *         compatibility_score: int|float,
             *         strengths_match?: array<int, string>,
             *         skills_gap?: array<int, string>,
             *         personalized_summary: string,
             *         link?: string
             *     }>,
             *     courses: array<int, array{
             *         title: string,
             *         company_or_platform?: string,
             *         compatibility_score: int|float,
             *         strengths_match?: array<int, string>,
             *         personalized_summary: string,
             *         link?: string
             *     }>,
             *     roadmap?: array<int, array{
             *         step_number: int,
             *         type: string,
             *         title: string,
             *         duration_weeks: int,
             *         description: string
             *     }>,
             *     general_summary?: string
             * } $data */
            $data = $response->toArray();

            return $data;
        } catch (\Exception $e) {
            return [
                'jobs' => [],
                'courses' => [],
                'general_summary' => 'Could not fetch recommendations at this time: ' . $e->getMessage()
            ];
        }
    }
}
