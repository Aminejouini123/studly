<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GroupsController extends AbstractController
{
    #[Route('/groups', name: 'app_groups')]
    public function index(): Response
    {
        return $this->render('groups/frontGroups.html.twig', [
            'controller_name' => 'GroupsController',
        ]);
    }

    #[Route('/admin/groups', name: 'app_admin_groups')]
    public function adminIndex(): Response
    {
        return $this->render('groups/backGroups.html.twig', [
            'controller_name' => 'GroupsController',
        ]);
    }
}
