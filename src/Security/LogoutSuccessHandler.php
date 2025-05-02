<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function onLogoutSuccess(Request $request): RedirectResponse
    {
        // Clear the session
        $request->getSession()->invalidate();
        
        // Clear all cookies
        $cookies = $request->cookies->all();
        foreach ($cookies as $name => $value) {
            setcookie($name, '', time() - 3600, '/');
        }
        
        // Redirect to login page
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
} 