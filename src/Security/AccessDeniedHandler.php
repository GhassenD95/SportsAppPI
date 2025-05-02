<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Security $security
    ) {}

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        // Get the user from the security context
        $user = $this->security->getUser();
        
        if ($user) {
            // If user is admin, redirect to admin dashboard
            if ($this->security->isGranted('ROLE_ADMIN')) {
                return new RedirectResponse($this->urlGenerator->generate('admin'));
            }
            
            // For all other authenticated users, redirect to home
            return new RedirectResponse($this->urlGenerator->generate('app_home'));
        }

        // If not authenticated, redirect to login
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
} 