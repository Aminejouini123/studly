<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $roles = $token->getRoleNames();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return new Response(null, 302, ['Location' => $this->router->generate('app_admin_user_index')]);
        }

        if (in_array('ROLE_ETUDIANT', $roles, true)) {
            // Adjust 'app_front' to your actual front office route name if different
            return new Response(null, 302, ['Location' => $this->router->generate('app_front')]);
        }

        // Default redirect if no specific role matched
        return new Response(null, 302, ['Location' => $this->router->generate('app_front')]);
    }
}
