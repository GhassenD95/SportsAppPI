<?php

namespace App\Controller\Frontoffice;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/frontoffice/profile')]
class ProfileController extends BaseController
{
    #[Route('/', name: 'frontoffice_profile', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('profile/show.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
} 