<?php

namespace App\Controller\Frontoffice;

use App\Repository\TeamRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/frontoffice/team')]
class TeamController extends BaseController
{
    #[Route('/', name: 'frontoffice_team_index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository): Response
    {
        return $this->renderFrontoffice('team/index.html.twig', [
            'teams' => $teamRepository->findAll(),
        ]);
    }
} 