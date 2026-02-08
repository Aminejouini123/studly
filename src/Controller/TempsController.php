<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TempsController extends AbstractController
{
    #[Route('/temps', name: 'app_temps')]
    public function index(): Response
    {
        return $this->render('temps/frontTemps.html.twig', [
            'controller_name' => 'TempsController',
        ]);
    }

    #[Route('/admin/temps', name: 'app_admin_temps')]
    public function adminIndex(): Response
    {
        return $this->render('temps/backTemps.html.twig', [
            'controller_name' => 'TempsController',
        ]);
    }
}
