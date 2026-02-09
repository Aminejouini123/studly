<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExamenController extends AbstractController
{
    #[Route('/examen', name: 'app_examen')]
    public function index(): Response
    {
        return $this->render('examen/index.html.twig', [
            'controller_name' => 'ExamenController',
        ]);
    }
}
