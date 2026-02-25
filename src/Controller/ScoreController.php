<?php

namespace App\Controller;

use App\Service\ScoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ScoreController extends AbstractController
{
    private ScoreService $scoreService;

    public function __construct(ScoreService $scoreService)
    {
        $this->scoreService = $scoreService;
    }

    #[Route('/my-score', name: 'app_my_score')]
    #[IsGranted('ROLE_ETUDIANT')]
    public function myScore(): Response
    {
        $user = $this->getUser();
        $score = $this->scoreService->getUserScore($user);

        return $this->render('score/my_score.html.twig', [
            'score' => $score,
            'user' => $user,
        ]);
    }

    #[Route('/admin/scores', name: 'app_admin_scores')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminScores(): Response
    {
        $scores = $this->scoreService->getAllUsersScores();

        return $this->render('score/admin_scores.html.twig', [
            'scores' => $scores,
        ]);
    }
}