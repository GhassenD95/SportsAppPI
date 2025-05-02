<?php

namespace App\Controller;

use App\Repository\TrainingRepository;
use App\Repository\TeamRepository;
use App\Repository\MatchEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    #[IsGranted('ROLE_USER')]
    public function index(
        TrainingRepository $trainingRepository,
        TeamRepository $teamRepository,
        MatchEventRepository $matchEventRepository
    ): Response {
        $user = $this->getUser();
        $data = [];

        // Common data for all roles
        $data['upcomingMatches'] = $matchEventRepository->findUpcoming(5);
        $data['recentTrainings'] = $trainingRepository->findRecent(5);

        // Role-specific data
        if ($this->isGranted('ROLE_ADMIN')) {
            $data['teams'] = $teamRepository->findAll();
            $data['allTrainings'] = $trainingRepository->findAll();
        } elseif ($this->isGranted('ROLE_COACH')) {
            $data['myTrainings'] = $trainingRepository->findByCoach($user);
            $data['myTeams'] = $teamRepository->findByCoach($user);
        } elseif ($this->isGranted('ROLE_PLAYER')) {
            $data['myTrainings'] = $trainingRepository->findByPlayer($user);
            $data['myTeam'] = $teamRepository->findByPlayer($user);
        }

        return $this->render('home/index.html.twig', [
            'data' => $data,
        ]);
    }
} 