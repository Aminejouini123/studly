<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ObjectivesController extends AbstractController
{
    #[Route('/objectives', name: 'app_objectives')]
    public function index(): Response
    {
        return $this->render('objectives/frontObjectives.html.twig', [
            'controller_name' => 'ObjectivesController',
        ]);
    }

    #[Route('/admin/objectives', name: 'app_admin_objectives')]
    public function adminIndex(): Response
    {
        return $this->render('objectives/backObjectives.html.twig', [
            'controller_name' => 'ObjectivesController',
        ]);
    }
}
