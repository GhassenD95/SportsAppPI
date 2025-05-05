<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $user = $this->getUser();
        
        // Redirect admin to /admin
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin');
        }

        // Render dashboard for other roles
        return $this->render('home/index.html.twig', [
            'user_role' => $user ? $user->getUserIdentifier() : null,
            'user_email' => $user ? $user->getEmail() : null
        ]);
    }
}
