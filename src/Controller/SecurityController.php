<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // Handle demo login requests
        if ($request->query->has('demo')) {
            $role = $request->query->get('demo');
            $credentials = $this->getDemoCredentials($role);

            return $this->render('security/login.html.twig', [
                'last_username' => $credentials['email'],
                'demo_password' => $credentials['password'],
                'error' => $authenticationUtils->getLastAuthenticationError(),
            ]);
        }

        // Normal login
        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'demo_password' => null,
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    private function getDemoCredentials(string $role): array
    {
        return match($role) {
            'admin' => [
                'email' => 'demo_admin@sports.com',
                'password' => 'demo_admin123'
            ],
            'manager' => [
                'email' => 'demo_manager@sports.com',
                'password' => 'demo_manager123'
            ],
            'coach' => [
                'email' => 'demo_coach@sports.com',
                'password' => 'demo_coach123'
            ],
            'athlete' => [
                'email' => 'demo_athlete@sports.com',
                'password' => 'demo_athlete123'
            ],
            default => [
                'email' => '',
                'password' => ''
            ],
        };
    }
}