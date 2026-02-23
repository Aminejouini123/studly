<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class RecommendationService
{
    private HttpClientInterface $client;
    private ParameterBagInterface $params;

    public function __construct(HttpClientInterface $client, ParameterBagInterface $params)
    {
        $this->client = $client;
        $this->params = $params;
    }

    public function getRecommendationsForUser(User $user): array
    {
        $profileData = [
            'skills' => $user->getSkills() ?? [],
            'educationLevel' => $user->getEducationLevel(),
            'jobTitle' => $user->getJobTitle(),
        ];

        try {
            // We assume the recommendation API runs on localhost:8002
            $response = $this->client->request('POST', 'http://localhost:8002/recommend', [
                'json' => [
                    'profile' => $profileData
                ],
                'timeout' => 30 // AI can be slow
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            return [
                'jobs' => [],
                'courses' => [],
                'general_summary' => 'Could not fetch recommendations at this time: ' . $e->getMessage()
            ];
        }
    }
}
